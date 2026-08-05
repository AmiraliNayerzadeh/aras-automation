<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $role->name }}</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('put')
                @include('admin.roles._form')
            </form>
        </div>
    </div>
</x-app-layout>
