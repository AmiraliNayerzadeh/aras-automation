<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('org_chart.title_index') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.org-chart.pdf') }}" target="_blank" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-file-pdf-2-line"></i> {{ __('org_chart.action_download_pdf') }}
                </a>
                <button type="button" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm" data-bs-toggle="modal" data-bs-target="#department-modal" data-mode="create">
                    <i class="ri-add-line"></i> {{ __('org_chart.action_add_department') }}
                </button>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('org_chart.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    <div class="alert alert-info-100 text-info-600 radius-8 mb-24 text-sm">
        <i class="ri-information-line"></i> {{ __('org_chart.multi_membership_note') }}
    </div>

    <div class="card radius-12 mb-24">
        <div class="card-header bg-base fw-semibold">{{ __('org_chart.section_chart_title') }}</div>
        <div class="card-body">
            <div id="org-chart-container" style="overflow: auto; min-height: 260px;"></div>
        </div>
    </div>

    <div class="card radius-12">
        <div class="card-header bg-base fw-semibold">{{ __('org_chart.section_manage_title') }}</div>
        <div class="card-body">
            @if ($roots->isEmpty())
                <p class="text-secondary-light mb-0">{{ __('org_chart.no_departments') }}</p>
            @else
                @include('admin.org-chart._tree', ['departments' => $roots, 'all' => $departments])
            @endif
        </div>
    </div>

    {{-- Department create/edit modal (shared, JS-populated) --}}
    <div class="modal fade" id="department-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.org-chart.departments.store') }}" id="department-form" class="modal-content">
                @csrf
                <input type="hidden" name="_method" id="department-method" value="POST">
                <div class="modal-header">
                    <h6 class="modal-title" id="department-modal-title">{{ __('org_chart.action_add_department') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <x-input-label for="department_name" :value="__('app.field_name')" />
                        <x-text-input id="department_name" name="name" class="mt-1 w-100" required />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="department_code" :value="__('app.field_code')" />
                        <x-text-input id="department_code" name="code" class="mt-1 w-100" required />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="department_branch" :value="__('app.field_branch')" />
                        <select id="department_branch" name="branch_id" class="form-select mt-1" required>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="department_parent" :value="__('org_chart.field_parent')" />
                        <select id="department_parent" name="parent_id" class="form-select mt-1">
                            <option value="">—</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add member modal (shared, JS-populated) --}}
    <div class="modal fade" id="member-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" id="member-form" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('org_chart.action_add_member') }} — <span id="member-department-name"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <x-input-label for="member_user_id" :value="__('org_chart.field_employee')" />
                    <select id="member_user_id" name="user_id" class="form-select mt-1" style="width: 100%;" required>
                        <option value=""></option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('org_chart.action_add_member') }}</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .oc-card { display: inline-block; min-width: 130px; padding: 10px 12px 8px; border-radius: 10px; background-color: #fff; box-shadow: 0 2px 8px rgba(20, 25, 60, .08); text-align: center; }
        .oc-card.oc-card-department { background-color: #2b3674; color: #fff; padding-top: 8px; }
        .oc-card.oc-card-root { background-color: #1b2559; color: #fff; font-weight: 600; }
        .oc-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #4318ff; margin-bottom: 6px; }
        .oc-name { display: block; font-weight: 600; font-size: 13px; color: inherit; line-height: 1.3; }
        .oc-meta { display: block; font-size: 11px; opacity: .75; margin-top: 2px; }
        .oc-accent { display: block; width: 28px; height: 3px; background-color: #4318ff; margin: 6px auto 0; border-radius: 2px; }
        .oc-card-department .oc-accent, .oc-card-root .oc-accent { background-color: #05cd99; }
    </style>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@6.0.0/dist/css/orgchart.min.css">
        <script src="https://cdn.jsdelivr.net/npm/orgchart@6.0.0/dist/js/orgchart.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
        <script>
            (function () {
                var chartData = @json($chartData);

                function escapeHtml(value) {
                    return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
                    });
                }

                function nodeTemplate(data) {
                    var cardClass = 'oc-card oc-card-' + (data.type || 'department');
                    var avatar = data.type === 'person'
                        ? '<img class="oc-avatar" src="' + escapeHtml(data.image) + '" alt="">'
                        : '';

                    return '<div class="' + cardClass + '">'
                        + avatar
                        + '<span class="oc-name">' + escapeHtml(data.name) + '</span>'
                        + (data.meta ? '<span class="oc-meta">' + escapeHtml(data.meta) + '</span>' : '')
                        + '<span class="oc-accent"></span>'
                        + '</div>';
                }

                if (chartData.children && chartData.children.length) {
                    new OrgChart({
                        chartContainer: '#org-chart-container',
                        data: chartData,
                        nodeTitle: 'name',
                        nodeTemplate: nodeTemplate,
                    });
                } else {
                    document.getElementById('org-chart-container').innerHTML =
                        '<p class="text-secondary-light text-center mb-0">' + @json(__('org_chart.no_departments')) + '</p>';
                }

                // Department modal: create / add-sub-department / edit, all through one form.
                var deptModal = document.getElementById('department-modal');
                var deptForm = document.getElementById('department-form');
                var deptMethod = document.getElementById('department-method');
                var deptTitle = document.getElementById('department-modal-title');
                var createUrl = @json(route('admin.org-chart.departments.store'));

                deptModal.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    var mode = trigger.getAttribute('data-mode');

                    deptForm.reset();

                    if (mode === 'edit') {
                        deptForm.action = trigger.getAttribute('data-action');
                        deptMethod.value = 'PUT';
                        deptTitle.textContent = @json(__('org_chart.action_edit'));
                        document.getElementById('department_name').value = trigger.getAttribute('data-name') || '';
                        document.getElementById('department_code').value = trigger.getAttribute('data-code') || '';
                        document.getElementById('department_branch').value = trigger.getAttribute('data-branch-id') || '';
                        document.getElementById('department_parent').value = trigger.getAttribute('data-parent-id') || '';
                    } else {
                        deptForm.action = createUrl;
                        deptMethod.value = 'POST';
                        deptTitle.textContent = @json(__('org_chart.action_add_department'));
                        document.getElementById('department_parent').value = trigger.getAttribute('data-parent-id') || '';
                        var branchId = trigger.getAttribute('data-branch-id');
                        if (branchId) {
                            document.getElementById('department_branch').value = branchId;
                        }
                    }
                });

                // Member modal.
                var memberModal = document.getElementById('member-modal');

                memberModal.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    document.getElementById('member-form').action = trigger.getAttribute('data-action');
                    document.getElementById('member-department-name').textContent = trigger.getAttribute('data-department-name');
                });

                memberModal.addEventListener('shown.bs.modal', function () {
                    var select = document.getElementById('member_user_id');
                    if (window.jQuery && jQuery.fn.select2 && !select.dataset.select2Ready) {
                        select.dataset.select2Ready = '1';
                        jQuery(select).select2({ dropdownParent: jQuery(memberModal), width: '100%' });
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
