@php($departments = $departments ?? collect())
@php($all = $all ?? collect())

@foreach ($departments as $department)
    <div class="border radius-8 p-12 mb-8 bg-base">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="ri-git-branch-line text-primary-600"></i>
                <span class="fw-semibold">{{ $department->name }}</span>
                <span class="text-secondary-light text-xs">({{ $department->code }})</span>
            </div>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary-600 radius-8"
                    data-bs-toggle="modal" data-bs-target="#member-modal"
                    data-action="{{ route('admin.org-chart.members.store', $department) }}"
                    data-department-name="{{ $department->name }}">
                    <i class="ri-user-add-line"></i> {{ __('org_chart.action_add_member') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary-600 radius-8"
                    data-bs-toggle="modal" data-bs-target="#department-modal"
                    data-mode="create" data-parent-id="{{ $department->id }}" data-branch-id="{{ $department->branch_id }}">
                    <i class="ri-add-line"></i> {{ __('org_chart.action_add_sub_department') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary-600 radius-8"
                    data-bs-toggle="modal" data-bs-target="#department-modal"
                    data-mode="edit" data-action="{{ route('admin.org-chart.departments.update', $department) }}"
                    data-name="{{ $department->name }}" data-code="{{ $department->code }}"
                    data-branch-id="{{ $department->branch_id }}" data-parent-id="{{ $department->parent_id }}"
                    title="{{ __('org_chart.action_edit') }}">
                    <i class="ri-edit-line"></i>
                </button>
                <form action="{{ route('admin.org-chart.departments.destroy', $department) }}" method="POST"
                    onsubmit="return confirm('{{ __('org_chart.confirm_delete_department') }}');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-sm btn-outline-danger-600 radius-8" title="{{ __('org_chart.action_delete') }}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </form>
            </div>
        </div>

        @if ($department->members->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mt-8">
                @foreach ($department->members as $member)
                    <div class="d-flex align-items-center gap-1 border radius-8 ps-4 pe-8 py-4 text-sm bg-neutral-50">
                        <img src="{{ $member->avatar_url }}" alt="" class="w-20-px h-20-px rounded-circle object-fit-cover">
                        {{ $member->name }}
                        <form action="{{ route('admin.org-chart.members.destroy', [$department, $member]) }}" method="POST" class="d-inline lh-1">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent text-danger-600 lh-1" title="{{ __('org_chart.action_remove') }}">
                                <i class="ri-close-line"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        @php($children = $all->where('parent_id', $department->id)->values())
        @if ($children->isNotEmpty())
            <div class="mt-8 ps-16 border-start">
                @include('admin.org-chart._tree', ['departments' => $children, 'all' => $all])
            </div>
        @endif
    </div>
@endforeach
