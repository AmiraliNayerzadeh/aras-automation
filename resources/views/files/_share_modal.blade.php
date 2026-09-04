@php
    $isFile = ($shareable ?? null) instanceof \App\Models\FileManager\FileEntry;
@endphp

@isset($shareable)
@once
    @push('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
        <style>
            .select2-avatar-img { width: 20px; height: 20px; border-radius: 50%; object-fit: cover; margin-inline-end: 8px; vertical-align: middle; }
            .select2-container .select2-selection--multiple,
            .select2-container .select2-selection--single { min-height: 42px; }
        </style>
        <script>
            (function () {
                var granteeBlocks = ['user', 'role', 'department', 'position'];

                document.addEventListener('change', function (event) {
                    if (!event.target.classList.contains('share-grantee-type')) {
                        return;
                    }
                    var form = event.target.closest('form');
                    var value = event.target.value;

                    granteeBlocks.forEach(function (type) {
                        var block = form.querySelector('.share-grantee-' + type);
                        block.style.display = value === type ? '' : 'none';
                        var select = block.querySelector('select');
                        select.disabled = value !== type;
                        if (window.jQuery && jQuery.fn.select2) {
                            jQuery(select).trigger('change.select2');
                        }
                    });
                });

                function renderUserOption(option) {
                    if (!option.id) {
                        return option.text;
                    }
                    var avatar = option.element ? option.element.getAttribute('data-avatar') : null;
                    var $wrap = jQuery('<span class="d-flex align-items-center"></span>');
                    if (avatar) {
                        $wrap.append(jQuery('<img>').attr('src', avatar).addClass('select2-avatar-img'));
                    }
                    $wrap.append(jQuery('<span></span>').text(option.text));
                    return $wrap;
                }

                function initShareSelects(modal) {
                    if (!window.jQuery || !jQuery.fn.select2) {
                        return;
                    }
                    var $modal = jQuery(modal);

                    $modal.find('.share-select2-user').each(function () {
                        if (this.dataset.select2Ready) {
                            return;
                        }
                        this.dataset.select2Ready = '1';
                        jQuery(this).select2({
                            dropdownParent: $modal,
                            width: '100%',
                            templateResult: renderUserOption,
                            templateSelection: renderUserOption,
                        });
                    });

                    $modal.find('.share-select2-plain').each(function () {
                        if (this.dataset.select2Ready) {
                            return;
                        }
                        this.dataset.select2Ready = '1';
                        jQuery(this).select2({ dropdownParent: $modal, width: '100%' });
                    });
                }

                document.addEventListener('shown.bs.modal', function (event) {
                    if (event.target.id && event.target.id.indexOf('share-modal-') === 0) {
                        initShareSelects(event.target);
                    }
                });
            })();
        </script>
    @endpush
@endonce

<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">{{ __('files.share_title') }} — {{ $isFile ? ($shareable->title ?: $shareable->original_name) : $shareable->name }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-16">
                    <div class="text-secondary-light text-sm mb-8">{{ __('files.share_current') }}</div>
                    @forelse ($shareable->shares as $share)
                        <div class="d-flex align-items-center justify-content-between border radius-8 px-12 py-8 mb-8">
                            <span class="text-sm">
                                @if ($share->grantee_type === 'everyone')
                                    <i class="ri-global-line"></i>
                                @elseif ($share->grantee_type === 'role')
                                    <i class="ri-shield-user-line"></i>
                                @elseif ($share->grantee_type === 'department')
                                    <i class="ri-git-branch-line"></i>
                                @elseif ($share->grantee_type === 'position')
                                    <i class="ri-briefcase-line"></i>
                                @else
                                    <i class="ri-user-line"></i>
                                @endif
                                {{ $share->label() }}
                            </span>
                            <form method="POST" action="{{ route($destroyRouteBase, [$destroyParam, $share]) }}">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-sm btn-outline-danger-600 radius-8 px-8 py-4">
                                    <i class="ri-close-line"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-secondary-light text-sm">{{ __('files.share_none') }}</p>
                    @endforelse
                </div>

                @php
                    $departments = $departments ?? \App\Models\Organization\Department::orderBy('name')->get(['id', 'name']);
                    $positions = $positions ?? \App\Models\Organization\Position::orderBy('title')->get(['id', 'title']);
                @endphp

                <form method="POST" action="{{ $storeRoute }}" class="share-add-form">
                    @csrf
                    <div class="mb-2">
                        <x-input-label :value="__('files.field_grantee_type')" />
                        <select name="grantee_type" class="form-select mt-1 share-grantee-type">
                            <option value="user">{{ __('files.grantee_type_user') }}</option>
                            <option value="role">{{ __('files.grantee_type_role') }}</option>
                            <option value="department">{{ __('files.grantee_type_department') }}</option>
                            <option value="position">{{ __('files.grantee_type_position') }}</option>
                            <option value="everyone">{{ __('files.grantee_type_everyone') }}</option>
                        </select>
                    </div>
                    <div class="mb-2 share-grantee-user">
                        <x-input-label :value="__('files.field_grantee_user')" />
                        <select name="grantee_value[]" class="form-select mt-1 share-select2-user" multiple>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-avatar="{{ $user->avatar_url }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 share-grantee-role" style="display: none;">
                        <x-input-label :value="__('files.field_grantee_role')" />
                        <select name="grantee_value" class="form-select mt-1 share-select2-plain" disabled>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 share-grantee-department" style="display: none;">
                        <x-input-label :value="__('files.field_grantee_department')" />
                        <select name="grantee_value" class="form-select mt-1 share-select2-plain" disabled>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 share-grantee-position" style="display: none;">
                        <x-input-label :value="__('files.field_grantee_position')" />
                        <select name="grantee_value" class="form-select mt-1 share-select2-plain" disabled>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary-600 radius-8 px-16 py-8 text-sm mt-1">{{ __('files.action_add_share') }}</button>
                </form>

                @if ($isFile)
                    <hr class="my-16">
                    <div class="text-secondary-light text-sm mb-8">{{ __('files.share_link_title') }}</div>
                    <p class="text-secondary-light text-xs">{{ __('files.share_link_hint') }}</p>
                    @if ($shareable->share_token)
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" readonly value="{{ route('files.link', $shareable->share_token) }}">
                            <button type="button" class="btn btn-outline-secondary-600" onclick="navigator.clipboard.writeText('{{ route('files.link', $shareable->share_token) }}')">{{ __('files.action_copy_link') }}</button>
                        </div>
                        @if ($shareable->share_token_expires_at)
                            <p class="text-secondary-light text-xs">{{ __('files.field_link_expiry') }}: {{ $shareable->share_token_expires_at->format('Y-m-d') }}</p>
                        @endif
                        <form method="POST" action="{{ route('files.entries.share-link.disable', $shareable) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('files.action_disable_link') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('files.entries.share-link.enable', $shareable) }}" class="d-flex gap-2 align-items-end">
                            @csrf
                            <div>
                                <x-input-label for="expiry-{{ $shareable->id }}" :value="__('files.field_link_expiry')" />
                                <input type="date" id="expiry-{{ $shareable->id }}" name="expires_at" class="form-control mt-1">
                            </div>
                            <button type="submit" class="btn btn-outline-primary-600 radius-8 px-16 py-8 text-sm">{{ __('files.action_enable_link') }}</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endisset
