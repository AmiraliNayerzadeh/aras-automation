<?php

namespace App\Services\Merchandising;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reads a CSV or spreadsheet into rows keyed by normalised heading
 * ("Item Main group Desc" becomes "item_main_group_desc"), so the importers
 * don't care which tool exported the file.
 */
class ImportRows
{
    /**
     * @return list<array<string, ?string>>
     */
    public static function fromCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = null;
        $rows = [];

        while (($line = fgetcsv($handle, escape: '\\')) !== false) {
            if ($header === null) {
                $line[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $line[0]);
                $header = array_map(fn ($h) => self::key((string) $h), $line);

                continue;
            }

            if ($line === [null]) {
                continue;
            }

            $rows[] = self::clean(array_combine($header, array_pad(array_slice($line, 0, count($header)), count($header), null)));
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Picks the sheet that looks most like the data asked for: the one whose
     * headings include $requiredHeading and that has the most rows. Product
     * workbooks from the ERP carry a dozen scratch sheets next to the master.
     *
     * @return list<array<string, ?string>>
     */
    public static function fromUpload(UploadedFile $file, string $requiredHeading): array
    {
        if (in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            return self::fromCsv($file->getRealPath());
        }

        $best = [];

        foreach (Excel::toArray(new class implements WithHeadingRow {}, $file) as $sheet) {
            $rows = array_map(fn ($row) => self::clean(array_combine(array_map(fn ($k) => self::key((string) $k), array_keys($row)), array_values($row))), $sheet);

            if ($rows && array_key_exists($requiredHeading, $rows[0]) && count($rows) > count($best)) {
                $best = $rows;
            }
        }

        return $best;
    }

    public static function key(string $heading): string
    {
        return trim(Str::of($heading)->lower()->replaceMatches('/[^a-z0-9]+/', '_')->toString(), '_');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, ?string>
     */
    private static function clean(array $row): array
    {
        return array_map(function ($value) {
            if ($value === null) {
                return null;
            }

            if (is_float($value) && floor($value) === $value) {
                $value = (string) (int) $value;
            }

            $value = trim(preg_replace('/\s+/u', ' ', (string) $value));

            return $value === '' || strtoupper($value) === 'NULL' ? null : $value;
        }, $row);
    }
}
