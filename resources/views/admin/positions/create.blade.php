<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.create_new') }} — {{ __('app.positions') }}</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.positions.store') }}">
                @csrf
                @include('admin.positions._form')
            </form>
        </div>
    </div>
</x-app-layout>
