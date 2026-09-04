@php($item = $item ?? null)

<div class="d-flex align-items-center">
    @foreach ($item->access_avatars ?? [] as $i => $avatarUser)
        <img src="{{ $avatarUser->avatar_url }}" alt="{{ $avatarUser->name }}" title="{{ $avatarUser->name }}"
            class="w-24-px h-24-px rounded-circle object-fit-cover border border-2 border-white"
            style="{{ $i > 0 ? 'margin-inline-start: -8px;' : '' }}">
    @endforeach
    @if (($item->access_avatars_extra ?? 0) > 0)
        <span class="w-24-px h-24-px rounded-circle bg-neutral-200 text-neutral-600 d-flex align-items-center justify-content-center text-xs fw-semibold border border-2 border-white" style="margin-inline-start: -8px;">
            +{{ $item->access_avatars_extra }}
        </span>
    @endif
    @if (($item->access_group_shares ?? 0) > 0)
        <i class="ri-group-line text-secondary-light ms-2" title="{{ __('files.access_shared_group', ['count' => $item->access_group_shares]) }}"></i>
    @endif
</div>
