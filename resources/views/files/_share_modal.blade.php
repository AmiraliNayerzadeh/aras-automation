@php($isFile = $shareable instanceof \App\Models\FileManager\FileEntry)

@once
    @push('scripts')
        <script>
            (function () {
                document.addEventListener('change', function (event) {
                    if (!event.target.classList.contains('share-grantee-type')) {
                        return;
                    }
                    var form = event.target.closest('form');
                    var userBlock = form.querySelector('.share-grantee-user');
                    var roleBlock = form.querySelector('.share-grantee-role');
                    var value = event.target.value;
                    userBlock.style.display = value === 'user' ? '' : 'none';
                    roleBlock.style.display = value === 'role' ? '' : 'none';
                    userBlock.querySelector('select').disabled = value !== 'user';
                    roleBlock.querySelector('select').disabled = value !== 'role';
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

                <form method="POST" action="{{ $storeRoute }}" class="share-add-form">
                    @csrf
                    <div class="mb-2">
                        <x-input-label :value="__('files.field_grantee_type')" />
                        <select name="grantee_type" class="form-select mt-1 share-grantee-type">
                            <option value="user">{{ __('files.grantee_type_user') }}</option>
                            <option value="role">{{ __('files.grantee_type_role') }}</option>
                            <option value="everyone">{{ __('files.grantee_type_everyone') }}</option>
                        </select>
                    </div>
                    <div class="mb-2 share-grantee-user">
                        <x-input-label :value="__('files.field_grantee_user')" />
                        <select name="grantee_value" class="form-select mt-1">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 share-grantee-role" style="display: none;">
                        <x-input-label :value="__('files.field_grantee_role')" />
                        <select name="grantee_value" class="form-select mt-1" disabled>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
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
