<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('products.title_edit') }} — {{ $product->title }}</h2>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('products.flash_'.str_replace('-', '_', str_replace(['products-', 'product-'], '', session('status')))) }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card radius-12">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        @include('admin.products._form')
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('products.history_title') }}</div>
                <div class="card-body" style="max-height: 520px; overflow-y: auto;">
                    @forelse ($activities as $activity)
                        @php($changes = $activity->changes())
                        <div class="d-flex gap-12 pb-16 mb-16 border-bottom">
                            <div class="w-32-px h-32-px radius-8 bg-primary-100 text-primary-600 d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="ri-history-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
                                    @if ($activity->causer)
                                        <img src="{{ $activity->causer->avatar_url }}" alt="" class="w-20-px h-20-px rounded-circle object-fit-cover">
                                        <span class="fw-medium text-sm">{{ $activity->causer->name }}</span>
                                    @endif
                                    <span class="text-secondary-light text-xs">{{ $activity->created_at->format('Y-m-d H:i') }}</span>
                                </div>

                                @if ($changes->get('attributes'))
                                    <ul class="mb-0 ps-16 text-sm">
                                        @foreach ($changes->get('attributes') as $field => $newValue)
                                            <li>
                                                <strong>{{ $field }}</strong>:
                                                {{ data_get($changes->get('old'), $field, '—') }} → {{ $newValue }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-secondary-light text-sm">{{ ucfirst($activity->description) }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ __('products.history_empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
