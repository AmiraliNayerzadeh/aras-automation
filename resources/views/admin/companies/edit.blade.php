<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $company->name }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.companies.update', $company) }}">
                @csrf
                @method('put')
                @include('admin.companies._form')
            </form>
        </div>
    </div>
</x-app-layout>
