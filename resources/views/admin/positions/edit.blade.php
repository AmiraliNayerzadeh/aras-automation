<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $position->title }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.positions.update', $position) }}">
                @csrf
                @method('put')
                @include('admin.positions._form')
            </form>
        </div>
    </div>
</x-app-layout>
