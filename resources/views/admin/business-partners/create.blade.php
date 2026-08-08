<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('business-partners.title_create') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.business-partners.store') }}">
                @csrf
                @include('admin.business-partners._form')
            </form>
        </div>
    </div>
</x-app-layout>
