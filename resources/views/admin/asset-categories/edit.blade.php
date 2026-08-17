<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $category->name }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.asset-categories.update', $category) }}">
                @csrf
                @method('put')
                @include('admin.asset-categories._form')
            </form>
        </div>
    </div>
</x-app-layout>
