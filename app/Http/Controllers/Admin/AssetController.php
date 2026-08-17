<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assets\Asset;
use App\Models\Assets\AssetAssignment;
use App\Models\Assets\AssetCategory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetController extends Controller implements HasMiddleware
{
    protected const STATUSES = ['in_use', 'in_storage', 'under_repair', 'retired', 'lost'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:assets.view', only: ['index', 'show']),
            new Middleware('permission:assets.create', only: ['create', 'store']),
            new Middleware('permission:assets.edit', only: ['edit', 'update']),
            new Middleware('permission:assets.delete', only: ['destroy']),
            new Middleware('permission:assets.assign', only: ['assign', 'return']),
            new Middleware('permission:assets.view', only: ['label', 'labels']),
        ];
    }

    public function index(Request $request): View
    {
        $assets = Asset::query()
            ->with(['category', 'currentHolder'])
            ->filter([
                'search' => $request->string('search')->toString(),
                'category_id' => $request->input('category_id'),
                'status' => $request->input('status'),
                'current_holder_id' => $request->input('current_holder_id'),
            ])
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.assets.index', [
            'assets' => $assets,
            'categories' => AssetCategory::orderBy('name')->get(),
            'holders' => User::orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.assets.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $asset = Asset::create($data + ['asset_code' => 'AST-PENDING-'.uniqid()]);
        $asset->update(['asset_code' => Asset::codeFor($asset->id)]);

        $imagePath = $this->storeImage($request, $asset);

        if ($imagePath) {
            $asset->update(['image_path' => $imagePath]);
        }

        return redirect()->route('admin.assets.show', $asset)->with('status', 'created');
    }

    public function show(Asset $asset): View
    {
        $asset->load(['category', 'currentHolder', 'assignments.user', 'assignments.assignedBy', 'assignments.returnedBy']);

        return view('admin.assets.show', [
            'asset' => $asset,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Asset $asset): View
    {
        return view('admin.assets.edit', ['asset' => $asset] + $this->formOptions());
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $asset->update($this->validateData($request, $asset));

        $imagePath = $this->storeImage($request, $asset);

        if ($imagePath) {
            $asset->update(['image_path' => $imagePath]);
        }

        return redirect()->route('admin.assets.show', $asset)->with('status', 'updated');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')->with('status', 'deleted');
    }

    public function assign(Request $request, Asset $asset): RedirectResponse
    {
        abort_if($asset->current_holder_id, 422, 'Asset is already assigned.');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'location' => ['required', Rule::in(['on_site', 'off_site'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $now = now();

        AssetAssignment::create([
            'asset_id' => $asset->id,
            'user_id' => $data['user_id'],
            'location' => $data['location'],
            'assigned_at' => $now,
            'assigned_by_id' => $request->user()->id,
            'note' => $data['note'] ?? null,
        ]);

        $asset->update([
            'current_holder_id' => $data['user_id'],
            'assigned_at' => $now,
            'status' => 'in_use',
        ]);

        return redirect()->route('admin.assets.show', $asset)->with('status', 'assigned');
    }

    public function return(Request $request, Asset $asset): RedirectResponse
    {
        $openAssignment = $asset->assignments()->whereNull('returned_at')->first();

        abort_unless($openAssignment, 422, 'Asset has no active assignment.');

        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $openAssignment->update([
            'returned_at' => now(),
            'returned_by_id' => $request->user()->id,
            'note' => trim($openAssignment->note.' '.($data['note'] ?? '')) ?: null,
        ]);

        $asset->update([
            'current_holder_id' => null,
            'assigned_at' => null,
            'status' => $data['status'],
        ]);

        return redirect()->route('admin.assets.show', $asset)->with('status', 'returned');
    }

    public function label(Asset $asset): Response
    {
        $pdf = Pdf::loadView('exports.asset-label', ['assets' => collect([$asset])])->setPaper('a4', 'portrait');

        return $pdf->stream($asset->asset_code.'.pdf');
    }

    public function labels(Request $request): Response
    {
        $ids = (array) $request->input('asset_ids', []);
        $assets = Asset::whereIn('id', $ids)->orderBy('title')->get();

        abort_if($assets->isEmpty(), 404);

        $pdf = Pdf::loadView('exports.asset-label', ['assets' => $assets])->setPaper('a4', 'portrait');

        return $pdf->stream('asset-labels.pdf');
    }

    protected function storeImage(Request $request, Asset $asset): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $request->validate(['image' => ['image', 'max:4096']]);

        return $request->file('image')->store('assets/'.$asset->id, 'public');
    }

    protected function validateData(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:asset_categories,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'categories' => AssetCategory::orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ];
    }
}
