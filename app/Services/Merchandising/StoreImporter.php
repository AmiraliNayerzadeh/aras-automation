<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising\Store;
use Illuminate\Support\Facades\DB;

/**
 * Creates or updates stores from any of three files, told apart by their
 * headings:
 *
 *  - the panel's own template (name, food_code, cleaning_code, ...);
 *  - the old visitor app's "accounts" export (food_acc_code, cleaning_acc_code, ...);
 *  - the ERP chart of accounts (Acc code, Account Name, ...), from which only
 *    customer accounts are taken.
 *
 * A store is identified by its ERP account codes, so the same store arriving
 * from several files, or the same file twice, is merged instead of duplicated.
 * The ERP export only fills gaps; the other two may also correct details.
 */
class StoreImporter
{
    /** Old visitor app ids. */
    private const VISITOR_REGIONS = [1 => 'Ararat', 2 => 'Ejmiacin', 3 => 'Erebuni', 4 => 'Kentron', 5 => 'Komitas', 6 => 'Kotayq', 7 => 'Lennakan', 8 => 'Malatia', 9 => 'Masiv', 10 => 'Vanadzor', 11 => 'Goris'];

    private const VISITOR_TYPES = [1 => 'general', 2 => 'restaurant', 3 => 'bakery', 4 => 'chain', 5 => 'supermarket', 6 => 'grocery', 7 => 'household', 8 => 'pharmacy', 9 => 'horeca', 10 => 'gas_station'];

    /** ERP customer-account prefixes that are stores (others are banks, oil, staff...). */
    private const ERP_STORE_PREFIXES = ['117', '118', '121', '122', '124'];

    /** @var array<string, Store> */
    private array $byCode = [];

    /** @var array<string, list<Store>> */
    private array $byName = [];

    /** @var array<int, true> Stores already written by an earlier row of this file. */
    private array $touched = [];

    /**
     * @param  list<array<string, ?string>>  $rows
     * @return array{format: string, created: int, updated: int, unchanged: int, skipped: int, conflicts: list<string>}
     */
    public function import(array $rows, ?int $userId = null): array
    {
        $format = $this->detectFormat($rows[0] ?? []);
        $stats = ['format' => $format, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'conflicts' => []];

        if ($format === 'unknown') {
            return $stats;
        }

        $records = match ($format) {
            'erp' => $this->fromErp($rows),
            'visitor' => array_map(fn ($row) => $this->fromVisitor($row), $rows),
            default => array_map(fn ($row) => $this->fromTemplate($row), $rows),
        };

        $this->loadIndex();
        $this->touched = [];

        DB::transaction(function () use ($records, $format, $userId, &$stats) {
            foreach ($records as $record) {
                if (! $record || ! $record['name'] || (! $record['food_account_code'] && ! $record['cleaning_account_code'] && $format !== 'template')) {
                    $stats['skipped']++;

                    continue;
                }

                $this->save($record, $format, $userId, $stats);
            }
        });

        return $stats;
    }

    /**
     * @param  array<string, ?string>  $row
     */
    public function detectFormat(array $row): string
    {
        return match (true) {
            array_key_exists('food_acc_code', $row) => 'visitor',
            array_key_exists('acc_code', $row) && array_key_exists('account_name', $row) => 'erp',
            array_key_exists('name', $row) && (array_key_exists('food_code', $row) || array_key_exists('cleaning_code', $row)) => 'template',
            default => 'unknown',
        };
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $stats
     */
    private function save(array $record, string $format, ?int $userId, array &$stats): void
    {
        $codes = array_values(array_unique(array_filter([$record['food_account_code'], $record['cleaning_account_code']])));
        $matches = collect($codes)->map(fn ($code) => $this->byCode[$code] ?? null)->filter()->unique('id');

        if ($matches->count() > 1) {
            $stats['conflicts'][] = $record['name'].' ('.implode(' / ', $codes).'): codes belong to different stores.';
            $stats['skipped']++;

            return;
        }

        // No code match: the ERP list and hand-made template rows may still
        // name a store that is missing this code. Visitor exports always
        // carry codes, so they never match by name.
        $store = $matches->first() ?? ($format !== 'visitor' ? $this->uniqueByName($record) : null);

        if (! $store) {
            $store = Store::create(array_filter($record, fn ($v) => $v !== null) + ['created_by' => $userId]);
            $this->index($store);
            $this->touched[$store->id] = true;
            $stats['created']++;

            return;
        }

        // A file listing the same store twice: the first row wins, otherwise
        // the two rows would overwrite each other on every import.
        // It may still bring a code the first row lacked. A code that fits
        // nowhere on that store means the file gives one code to two shops.
        if (isset($this->touched[$store->id])) {
            foreach (['food_account_code', 'cleaning_account_code'] as $field) {
                $code = $record[$field];

                if ($code && blank($store->{$field}) && ! isset($this->byCode[$code])) {
                    $store->{$field} = $code;
                }
            }

            $store->isDirty() ? $store->save() : null;
            $this->index($store);

            $stray = array_diff($codes, [$store->food_account_code, $store->cleaning_account_code]);

            if ($stray) {
                $stats['conflicts'][] = $record['name'].' ('.implode(' / ', $codes).'): shares a code with "'.$store->name.'" earlier in this file.';
                $stats['skipped']++;
            } else {
                $stats['unchanged']++;
            }

            return;
        }

        $this->touched[$store->id] = true;

        foreach ($record as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $isCode = in_array($field, ['food_account_code', 'cleaning_account_code'], true);

            // The ERP export only fills gaps; codes never move between stores.
            if (($format === 'erp' || $isCode) && filled($store->{$field})) {
                continue;
            }

            if ($isCode && isset($this->byCode[$value]) && $this->byCode[$value]->id !== $store->id) {
                continue;
            }

            $store->{$field} = $value;
        }

        if ($store->isDirty()) {
            $store->save();
            $this->index($store);
            $stats['updated']++;
        } else {
            $stats['unchanged']++;
        }
    }

    /**
     * An ERP account whose code isn't known yet joins an existing store only
     * when exactly one store has that exact name and its slot for that
     * division is still empty; common names never get merged by guesswork.
     *
     * @param  array<string, mixed>  $record
     */
    private function uniqueByName(array $record): ?Store
    {
        $candidates = $this->byName[self::name($record['name'])] ?? [];

        if (count($candidates) !== 1) {
            return null;
        }

        $store = $candidates[0];

        foreach (['food_account_code', 'cleaning_account_code'] as $field) {
            if ($record[$field] && filled($store->{$field})) {
                return null;
            }
        }

        return $store;
    }

    /**
     * @param  list<array<string, ?string>>  $rows
     * @return list<array<string, mixed>>
     */
    private function fromErp(array $rows): array
    {
        $accounts = [];

        foreach ($rows as $row) {
            $code = self::code($row['acc_code'] ?? null);

            if (! $code || ! in_array(substr($code, 0, 3), self::ERP_STORE_PREFIXES, true) || ! ($row['account_name'] ?? null)) {
                continue;
            }

            [$lat, $lng] = self::coordinates($row['address2'] ?? null);

            $accounts[] = [
                'name' => $row['account_name'],
                'division' => self::erpDivision($code),
                'code' => $code,
                'tax_code' => $row['tax_code'] ?? $row['taxcode'] ?? null,
                'address' => $row['address1'] ?? null,
                'phone' => collect([$row['tel1'] ?? null, $row['tel2'] ?? null, $row['mobile'] ?? null])->filter()->unique()->implode(', ') ?: null,
                'latitude' => $lat,
                'longitude' => $lng,
            ];
        }

        // A shop's food and household accounts are separate rows with the
        // same name; pair them up when that pairing is unambiguous.
        $groups = collect($accounts)->groupBy(fn ($a) => self::name($a['name']));
        $records = [];

        foreach ($groups as $group) {
            $food = $group->where('division', 'food');
            $cleaning = $group->where('division', 'cleaning');

            if ($food->count() === 1 && $cleaning->count() === 1 && $group->count() === 2) {
                $records[] = $this->erpRecord($food->first(), $cleaning->first()['code']);

                continue;
            }

            foreach ($group as $account) {
                $records[] = $this->erpRecord($account);
            }
        }

        return $records;
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>
     */
    private function erpRecord(array $account, ?string $cleaningCode = null): array
    {
        return [
            'name' => $account['name'],
            'food_account_code' => in_array($account['division'], ['food', 'both'], true) ? $account['code'] : null,
            'cleaning_account_code' => $cleaningCode ?? (in_array($account['division'], ['cleaning', 'both'], true) ? $account['code'] : null),
            'tax_code' => $account['tax_code'],
            'type' => str_starts_with($account['code'], '124') ? 'gas_station' : null,
            'address' => $account['address'],
            'phone' => $account['phone'],
            'latitude' => $account['latitude'],
            'longitude' => $account['longitude'],
            'status' => 'active',
        ];
    }

    /**
     * @param  array<string, ?string>  $row
     * @return array<string, mixed>
     */
    private function fromVisitor(array $row): array
    {
        $lat = (float) ($row['latitude'] ?? 0);
        $lng = (float) ($row['longitude'] ?? 0);

        return [
            'name' => $row['name'] ?? null,
            'food_account_code' => self::code($row['food_acc_code'] ?? null),
            'cleaning_account_code' => self::code($row['cleaning_acc_code'] ?? null),
            'tax_code' => $row['tax_code'] ?? null,
            'type' => self::VISITOR_TYPES[(int) ($row['account_type_id'] ?? 0)] ?? null,
            'region' => self::VISITOR_REGIONS[(int) ($row['neighborhood_id'] ?? 0)] ?? null,
            'address' => isset($row['address']) ? mb_substr($row['address'], 0, 500) : null,
            'phone' => $row['phone'] ?? null,
            'latitude' => $lat && $lng ? $lat : null,
            'longitude' => $lat && $lng ? $lng : null,
            'is_vip' => ($row['is_vip'] ?? '0') === '1',
            'status' => self::status($row['status'] ?? null),
            'legacy_account_id' => isset($row['id']) ? (int) $row['id'] : null,
        ];
    }

    /**
     * @param  array<string, ?string>  $row
     * @return array<string, mixed>
     */
    private function fromTemplate(array $row): array
    {
        $type = strtolower(str_replace([' ', '-'], '_', (string) ($row['type'] ?? '')));

        return [
            'name' => $row['name'] ?? null,
            'food_account_code' => self::code($row['food_code'] ?? null),
            'cleaning_account_code' => self::code($row['cleaning_code'] ?? null),
            'tax_code' => $row['tax_code'] ?? null,
            'type' => in_array($type, Store::TYPES, true) ? $type : null,
            'city' => $row['city'] ?? null,
            'region' => $row['region'] ?? null,
            'address' => isset($row['address']) ? mb_substr($row['address'], 0, 500) : null,
            'phone' => $row['phone'] ?? null,
            'is_vip' => isset($row['vip']) ? in_array(strtolower($row['vip']), ['1', 'yes', 'true', 'vip'], true) : null,
            'status' => isset($row['status']) ? self::status($row['status']) : null,
        ];
    }

    private function loadIndex(): void
    {
        $this->byCode = [];
        $this->byName = [];

        Store::withTrashed()->get()->each(fn (Store $store) => $this->index($store));
    }

    private function index(Store $store): void
    {
        foreach ([$store->food_account_code, $store->cleaning_account_code] as $code) {
            if ($code) {
                $this->byCode[$code] = $store;
            }
        }

        $key = self::name($store->name);
        $this->byName[$key] = collect($this->byName[$key] ?? [])->push($store)->unique('id')->values()->all();
    }

    /**
     * Food accounts are 117..., household 118...; among 122... the fourth
     * digit is 0 for food, and 121/124 chains use one account for both.
     */
    private static function erpDivision(string $code): string
    {
        return match (substr($code, 0, 3)) {
            '117' => 'food',
            '118' => 'cleaning',
            '122' => $code[3] === '0' ? 'food' : 'cleaning',
            default => 'both',
        };
    }

    private static function code(?string $value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^\d{5,12}$/', $value) ? $value : null;
    }

    private static function name(?string $value): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) $value), " .-\t"));
    }

    private static function status(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, Store::STATUSES, true) ? $value : ($value === '' ? 'active' : 'suspended');
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private static function coordinates(?string $value): array
    {
        if ($value && preg_match('/^\s*(-?\d{1,2}\.\d+)\s*,\s*(-?\d{1,3}\.\d+)\s*$/', $value, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        return [null, null];
    }
}
