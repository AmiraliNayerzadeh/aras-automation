<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $brand->title }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.product-brands.update', $brand) }}">
                @csrf
                @method('put')
                @include('admin.product-brands._form')
            </form>
        </div>
    </div>
</x-app-layout>
