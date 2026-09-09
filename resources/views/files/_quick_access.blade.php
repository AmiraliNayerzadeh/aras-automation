@php($quickAccess = $quickAccess ?? collect())

<div class="card radius-12 mb-24">
    <div class="card-body py-12">
        <div class="d-flex align-items-center gap-8 mb-8">
            <i class="ri-pushpin-2-line text-warning-main"></i>
            <span class="text-sm fw-semibold">{{ __('files.quick_access_title') }}</span>
        </div>
        @if ($quickAccess->isEmpty())
            <p class="text-secondary-light text-xs mb-0">{{ __('files.quick_access_empty') }}</p>
        @else
            <div class="d-flex flex-wrap gap-8">
                @foreach ($quickAccess as $pinned)
                    @php($isPinnedFolder = $pinned instanceof \App\Models\FileManager\Folder)
                    <a href="{{ $isPinnedFolder ? route('files.index', ['folder' => $pinned->id]) : route('files.entries.show', $pinned) }}"
                        class="d-flex align-items-center gap-1 border radius-8 px-12 py-6 text-sm text-secondary-light text-decoration-none">
                        <i class="{{ $isPinnedFolder ? 'ri-folder-3-fill text-warning-main' : $pinned->iconClass().' '.$pinned->iconColorClass() }}"></i>
                        {{ $isPinnedFolder ? $pinned->name : ($pinned->title ?: $pinned->original_name) }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
