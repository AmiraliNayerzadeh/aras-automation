<?php

namespace App\Http\Controllers\Merchandising;

use App\Enums\MerchandisingDivision;
use App\Http\Controllers\Controller;
use App\Models\Merchandising\Store;
use App\Models\Merchandising\Upload;
use App\Models\Merchandising\Visit;
use App\Models\Merchandising\VisitItem;
use App\Models\Products\Product;
use App\Models\User;
use App\Services\Merchandising\MerchandisingOptions;
use App\Services\Merchandising\VisitFiler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VisitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:merchandising.create|merchandising.view_all', only: ['index', 'show']),
            new Middleware('permission:merchandising.create', only: ['create', 'store', 'stores']),
            new Middleware('permission:merchandising.view_all', only: ['destroy']),
        ];
    }

    public function __construct(private readonly MerchandisingOptions $options) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $visits = Visit::query()
            ->visibleTo($user)
            ->with(['store', 'user'])
            ->withCount(['items', 'media', 'items as out_of_stock_count' => fn ($q) => $q->whereIn('stock_status', ['out_of_stock', 'not_listed'])])
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('visited_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('visited_at', '<=', $request->input('date_to')))
            ->when($request->filled('user_id') && $user->can('merchandising.view_all'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('store', fn ($s) => $s->search($request->input('q'))))
            ->latest('visited_at')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising.visits.index', [
            'visits' => $visits,
            'merchandisers' => $user->can('merchandising.view_all') ? User::permission('merchandising.create')->orderBy('name')->get(['id', 'name']) : collect(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('merchandising.visits.create', [
            'catalog' => $this->options->catalogFor($user),
            'divisions' => $this->options->divisionsFor($user),
            'issues' => $this->options->lookup('merch_issue'),
            'actions' => $this->options->lookup('merch_action'),
            'feedback' => $this->options->lookup('merch_feedback'),
            'now' => $this->options->now()->format('Y-m-d\TH:i'),
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    /**
     * The form posts one JSON "payload" field: a visit can carry hundreds of
     * product checks, which is past PHP's max_input_vars as separate fields.
     */
    public function store(Request $request, VisitFiler $filer): RedirectResponse
    {
        $user = $request->user();
        $payload = json_decode((string) $request->input('payload'), true);

        if (! is_array($payload)) {
            throw ValidationException::withMessages(['payload' => __('merchandising.error_payload')]);
        }

        // A resubmitted form (double tap, flaky network) lands on the visit it already created.
        $existing = Visit::where('submission_key', $payload['submission_key'] ?? '')->where('user_id', $user->id)->first();

        if ($existing) {
            return redirect()->route('merchandising.visits.show', $existing)->with('status', 'visit-saved');
        }

        $data = $this->validatePayload($payload, $user);
        $uploads = Upload::whereIn('token', $data['uploads'])->where('user_id', $user->id)
            ->whereNull('visit_id')->whereNotNull('completed_at')->orderBy('id')->get();

        foreach (['before', 'after'] as $kind) {
            if (! $uploads->contains('kind', $kind)) {
                throw ValidationException::withMessages(['uploads' => __('merchandising.error_photo_'.$kind)]);
            }
        }

        $stored = [];

        try {
            $visit = DB::transaction(function () use ($data, $user, $uploads, $filer, &$stored) {
                $visit = Visit::create([
                    'submission_key' => $data['submission_key'],
                    'user_id' => $user->id,
                    'store_id' => $this->resolveStore($data, $user)->id,
                    'visited_at' => $data['visited_at'],
                    'actions' => $data['actions'] ?? [],
                    'store_feedback' => $data['store_feedback'] ?? [],
                    'needs_order' => (bool) ($data['needs_order'] ?? false),
                    'note' => $data['note'] ?? null,
                ]);

                $divisions = Product::with('category:id,division')
                    ->whereIn('id', collect($data['items'] ?? [])->pluck('product_id')->filter())
                    ->get()->mapWithKeys(fn (Product $p) => [$p->id => $p->category?->division]);

                foreach ($data['items'] ?? [] as $item) {
                    $visit->items()->create([
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => empty($item['product_id']) ? $item['product_name'] : null,
                        'division' => isset($item['product_id']) ? $divisions[$item['product_id']] ?? null : ($item['division'] ?? null),
                        'stock_status' => $item['stock_status'] ?? null,
                        'shelf_position' => $item['shelf_position'] ?? null,
                        'price_tag' => $item['price_tag'] ?? null,
                        'issues' => array_values(array_unique($item['issues'] ?? [])),
                        'needs_order' => (bool) ($data['needs_order'] ?? false) && (bool) ($item['needs_order'] ?? false),
                    ]);
                }

                $visit->forceFill(['divisions' => $visit->items()->whereNotNull('division')->distinct()->pluck('division')->values()->all()])->save();
                $stored = $filer->file($visit, $uploads);

                return $visit;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($stored);

            throw $e;
        }

        Storage::disk('local')->delete($uploads->pluck('temp_path')->all());

        return redirect()->route('merchandising.visits.show', $visit)->with('status', 'visit-saved');
    }

    public function show(Request $request, Visit $visit): View
    {
        abort_unless(Visit::whereKey($visit->id)->visibleTo($request->user())->exists(), 403);

        $visit->load(['store', 'user', 'items.product', 'media.fileEntry', 'folder']);

        return view('merchandising.visits.show', [
            'visit' => $visit,
            'issues' => $this->options->lookup('merch_issue', activeOnly: false),
            'actions' => $this->options->lookup('merch_action', activeOnly: false),
            'feedback' => $this->options->lookup('merch_feedback', activeOnly: false),
        ]);
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $visit->delete();

        return redirect()->route('merchandising.visits.index')->with('status', 'visit-deleted');
    }

    /**
     * Store search for the form: active, listed stores first, by name or code.
     */
    public function stores(Request $request): JsonResponse
    {
        $term = mb_substr(trim((string) $request->query('q')), 0, 100);

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $stores = Store::query()
            ->where('status', 'active')
            ->search($term)
            ->orderByDesc('is_verified')
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$term.'%'])
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'food_account_code', 'cleaning_account_code', 'city', 'region', 'address', 'type']);

        return response()->json($stores->map(fn (Store $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'codes' => $s->codes(),
            'place' => collect([$s->city, $s->region])->filter()->implode(', '),
            'address' => $s->address,
        ]));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function validatePayload(array $payload, User $user): array
    {
        $issueCodes = array_keys($this->options->lookup('merch_issue'));
        $validator = Validator::make($payload, [
            'submission_key' => ['required', 'uuid'],
            'visited_at' => ['required', 'date_format:Y-m-d\TH:i', 'before_or_equal:'.$this->options->now()->addMinutes(10)->format('Y-m-d H:i')],
            'store_id' => ['nullable', 'integer', Rule::exists('stores', 'id')->whereNull('deleted_at')],
            'store_name' => ['nullable', 'required_without:store_id', 'string', 'max:255'],
            'items' => ['nullable', 'array', 'max:500'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_name' => ['nullable', 'required_without:items.*.product_id', 'string', 'max:255'],
            'items.*.division' => ['nullable', Rule::in(MerchandisingDivision::values())],
            'items.*.stock_status' => ['nullable', Rule::in(VisitItem::STOCK_STATUSES)],
            'items.*.shelf_position' => ['nullable', Rule::in(VisitItem::SHELF_POSITIONS)],
            'items.*.price_tag' => ['nullable', Rule::in(VisitItem::PRICE_TAGS)],
            'items.*.issues' => ['nullable', 'array'],
            'items.*.issues.*' => [Rule::in($issueCodes)],
            'items.*.needs_order' => ['nullable', 'boolean'],
            'actions' => ['nullable', 'array'],
            'actions.*' => [Rule::in(array_keys($this->options->lookup('merch_action')))],
            'store_feedback' => ['nullable', 'array'],
            'store_feedback.*' => [Rule::in(array_keys($this->options->lookup('merch_feedback')))],
            'needs_order' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:200'],
            'uploads' => ['required', 'array', 'min:2', 'max:200'],
            'uploads.*' => ['uuid'],
        ], [], [
            'visited_at' => __('merchandising.field_visited_at'),
            'store_name' => __('merchandising.field_store'),
            'note' => __('merchandising.field_note'),
        ]);

        if ($validator->fails()) {
            // The handler flashes the raw payload back, so the form restores itself.
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * A store typed in by hand is kept, marked unverified, so the admin can
     * review it; the same typed name is reused instead of piling up copies.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveStore(array $data, User $user): Store
    {
        if (! empty($data['store_id'])) {
            return Store::findOrFail($data['store_id']);
        }

        $name = trim(preg_replace('/\s+/', ' ', $data['store_name']));

        return Store::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->where('is_verified', false)->first()
            ?? Store::create(['name' => $name, 'is_verified' => false, 'status' => 'active', 'created_by' => $user->id]);
    }
}
