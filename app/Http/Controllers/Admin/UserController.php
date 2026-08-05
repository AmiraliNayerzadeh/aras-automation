<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\Organization\Unit;
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
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
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

        $user = User::create($data);

        if ($request->filled('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return redirect()->route('admin.users.index')->with('status', 'user-created');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', $this->formOptions() + ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $this->validateData($request, $user);

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
}
