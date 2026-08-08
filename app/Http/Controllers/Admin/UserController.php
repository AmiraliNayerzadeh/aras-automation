<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Organization\Branch;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\Organization\Unit;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with(['department', 'position'])
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%");
            }))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->input('department_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('role'), fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', $request->input('role'))))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users] + $this->filterOptions());
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $this->validateData($request);
        $avatarPath = $this->storeAvatar($request);

        if ($avatarPath) {
            $data['profile_photo_path'] = $avatarPath;
        }

        $user = User::create($data);

        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return redirect()->route('admin.users.index')->with('status', 'user-created');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load(['documents.category', 'documents.uploadedBy']);

        return view('admin.users.edit', $this->formOptions() + [
            'user' => $user,
            'documentCategories' => LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'file_category'))
                ->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $this->validateData($request, $user);
        $avatarPath = $this->storeAvatar($request, $user);

        if ($avatarPath) {
            $data['profile_photo_path'] = $avatarPath;
        }

        $user->update($data);

        if ($request->has('roles') && $request->user()->can('roles.manage')) {
            $user->syncRoles($request->input('roles', []));
        }

        return redirect()->route('admin.users.index')->with('status', 'user-updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'user-deleted');
    }

    public function storeDocument(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'category_lookup_value_id' => ['nullable', 'exists:lookup_values,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store("documents/user/{$user->id}", 'public');

        $user->documents()->create([
            'category_lookup_value_id' => $data['category_lookup_value_id'] ?? null,
            'title' => $data['title'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.users.edit', $user)->with('status', 'document-uploaded');
    }

    public function destroyDocument(User $user, Document $document): RedirectResponse
    {
        $this->authorize('update', $user);
        abort_unless($document->documentable_id === $user->id && $document->documentable_type === 'user', 404);

        $document->delete();

        return redirect()->route('admin.users.edit', $user)->with('status', 'document-deleted');
    }

    protected function storeAvatar(Request $request, ?User $user = null): ?string
    {
        if (! $request->hasFile('avatar')) {
            return null;
        }

        $request->validate(['avatar' => ['image', 'max:4096']]);

        return $request->file('avatar')->store('avatars/'.($user?->id ?? 'new'), 'public');
    }

    protected function validateData(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'employee_number' => ['nullable', 'string', 'max:255', 'unique:users,employee_number,'.($user?->id)],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(UserStatus::cases(), 'value'))],
            'employment_type' => ['nullable', 'string', 'in:'.implode(',', array_column(EmploymentType::cases(), 'value'))],
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'locale' => ['required', 'string', 'in:en,hy,fa'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'companies' => Company::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'positions' => Position::orderBy('title')->get(),
            'managers' => User::orderBy('name')->get(['id', 'name']),
            'roles' => Role::orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
            'employmentTypes' => EmploymentType::cases(),
        ];
    }

    protected function filterOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
            'roles' => Role::orderBy('name')->get(),
        ];
    }
}
