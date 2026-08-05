<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.create_new') }} — {{ __('app.units') }}</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.units.store') }}">
                @csrf
                @include('admin.units._form')
            </form>
        </div>
    </div>
</x-app-layout>
