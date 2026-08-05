<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $department->name }}</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                @csrf
                @method('put')
                @include('admin.departments._form')
            </form>
        </div>
    </div>
</x-app-layout>
