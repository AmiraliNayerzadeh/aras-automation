<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('assets.title_edit') }} — {{ $asset->title }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.assets.update', $asset) }}" enctype="multipart/form-data">
                @csrf
                @method('put')
                @include('admin.assets._form')
            </form>
        </div>
    </div>
</x-app-layout>
