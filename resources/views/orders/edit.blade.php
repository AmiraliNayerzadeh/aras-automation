<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('orders.title_edit') }} — {{ $order->order_number }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('orders.update', $order) }}">
                @csrf
                @method('put')
                @include('orders._form')
            </form>
        </div>
    </div>
</x-app-layout>
