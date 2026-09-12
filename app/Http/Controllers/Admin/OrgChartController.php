<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrgChartController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:org-chart.manage'),
        ];
    }

    public function index(): View
    {
        $departments = Department::with('members.position')->orderBy('name')->get();
        $roots = $departments->whereNull('parent_id')->values();

        // The orgchart library renders a single root node, so multiple
        // top-level departments are wrapped under one synthetic company node.
        $chartData = [
            'type' => 'root',
            'name' => config('app.name'),
            'meta' => '',
            'children' => $roots->map(fn (Department $d) => $this->buildChartNode($d, $departments))->values(),
        ];

        return view('admin.org-chart.index', [
            'chartData' => $chartData,
            'departments' => $departments,
            'roots' => $roots,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * @param  Collection<int, Department>  $all
     * @param  (callable(User): ?string)|null  $imageResolver
     * @return array<string, mixed>
     */
    protected function buildChartNode(Department $department, Collection $all, ?callable $imageResolver = null): array
    {
        $imageResolver ??= fn (User $user) => $user->avatar_url;

        $childDepartments = $all->where('parent_id', $department->id)
            ->map(fn (Department $child) => $this->buildChartNode($child, $all, $imageResolver))
            ->values();

        $memberNodes = $department->members->map(fn (User $user) => [
            'type' => 'person',
            'name' => $user->name,
            'meta' => $user->position?->title ?? __('org_chart.employee'),
            'image' => $imageResolver($user),
            'children' => [],
        ])->values();

        return [
            'type' => 'department',
            'name' => $department->name,
            'meta' => __('org_chart.member_count', ['count' => $department->members->count()]),
            'children' => $childDepartments->concat($memberNodes)->values(),
        ];
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        Department::create($this->validateDepartment($request));

        return redirect()->route('admin.org-chart.index')->with('status', 'department-created');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validateDepartment($request, $department));

        return redirect()->route('admin.org-chart.index')->with('status', 'department-updated');
    }

    public function destroyDepartment(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('admin.org-chart.index')->with('status', 'department-deleted');
    }

    protected function validateDepartment(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'parent_id' => [
                'nullable',
                'exists:departments,id',
                Rule::notIn($department ? [$department->id] : []),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
        ]);
    }

    public function addMember(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $department->members()->syncWithoutDetaching([$data['user_id']]);

        return redirect()->route('admin.org-chart.index')->with('status', 'member-added');
    }

    public function removeMember(Department $department, User $user): RedirectResponse
    {
        $department->members()->detach($user->id);

        return redirect()->route('admin.org-chart.index')->with('status', 'member-removed');
    }

    public function pdf(): Response
    {
        $departments = Department::with('members.position')->orderBy('name')->get();
        $roots = $departments->whereNull('parent_id')->values();

        $photos = $departments->flatMap->members->unique('id')
            ->mapWithKeys(fn (User $user) => [$user->id => $this->photoDataUri($user)]);

        $defaultPhotoPath = public_path('assets/user-default.jpg');
        $defaultPhoto = is_file($defaultPhotoPath)
            ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($defaultPhotoPath))
            : null;

        $treeData = [
            'type' => 'root',
            'name' => config('app.name'),
            'meta' => '',
            'children' => $roots->map(fn (Department $d) => $this->buildChartNode(
                $d,
                $departments,
                fn (User $user) => $photos[$user->id] ?? $defaultPhoto
            ))->values(),
        ];

        $pdf = Pdf::loadView('exports.org-chart-pdf', [
            'roots' => $roots,
            'all' => $departments,
            'photos' => $photos,
            'defaultPhoto' => $defaultPhoto,
            'treeData' => $treeData,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('org-chart.pdf');
    }

    private function photoDataUri(User $user): ?string
    {
        if (! $user->profile_photo_path || ! Storage::disk('public')->exists($user->profile_photo_path)) {
            return null;
        }

        $path = Storage::disk('public')->path($user->profile_photo_path);
        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
