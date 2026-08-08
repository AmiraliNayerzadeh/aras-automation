<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('products.title_create') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.products._form')
            </form>
        </div>
    </div>
</x-app-layout>
