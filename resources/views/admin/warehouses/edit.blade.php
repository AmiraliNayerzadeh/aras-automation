<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('warehouse.title_edit') }} — {{ $warehouse->name }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.warehouses.update', $warehouse) }}">
                @csrf
                @method('put')
                @include('admin.warehouses._form')
            </form>
        </div>
    </div>
</x-app-layout>
