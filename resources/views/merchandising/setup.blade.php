<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('merchandising.title_setup') }}</h2>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('merchandising.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card radius-12 mb-24">
                <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2"><i class="ri-user-star-line text-primary-600"></i> {{ __('merchandising.setup_merchandisers') }}</div>
                <div class="card-body">
                    <p class="text-secondary-light text-sm">{{ __('merchandising.setup_merchandisers_hint') }}</p>

                    @forelse ($merchandisers as $merchandiser)
                        <div class="d-flex align-items-center gap-3 flex-wrap py-12 border-bottom">
                            <img src="{{ $merchandiser->avatar_url }}" alt="" class="w-40-px h-40-px rounded-circle object-fit-cover">
                            <div class="fw-medium flex-grow-1">{{ $merchandiser->name }}</div>
                            <form method="POST" action="{{ route('merchandising.setup.merchandisers.update', $merchandiser) }}" class="d-flex align-items-center gap-3">
                                @csrf @method('put')
                                @foreach (\App\Enums\MerchandisingDivision::cases() as $division)
                                    <label class="d-flex align-items-center gap-1 text-sm">
                                        <input type="checkbox" class="form-check-input mt-0" name="divisions[]" value="{{ $division->value }}" @checked(in_array($division->value, (array) $merchandiser->merchandising_divisions, true)) onchange="this.form.submit()">
                                        {{ $division->label() }}
                                    </label>
                                @endforeach
                            </form>
                            <form method="POST" action="{{ route('merchandising.setup.merchandisers.destroy', $merchandiser) }}" onsubmit="return confirm(@js(__('merchandising.setup_confirm_remove')));">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger-600 radius-8" title="{{ __('merchandising.setup_remove') }}"><i class="ri-user-unfollow-line"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-secondary-light mb-0">{{ __('merchandising.setup_no_merchandisers') }}</p>
                    @endforelse

                    <form method="POST" action="{{ route('merchandising.setup.merchandisers.store') }}" class="d-flex gap-2 mt-16">
                        @csrf
                        <select name="user_id" class="form-select" required>
                            <option value="">{{ __('merchandising.setup_add_placeholder') }}</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary-600 radius-8 px-16 flex-shrink-0"><i class="ri-user-add-line"></i> {{ __('merchandising.setup_add') }}</button>
                    </form>
                </div>
            </div>

            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2"><i class="ri-file-excel-2-line text-success-600"></i> {{ __('merchandising.setup_products_import') }}
                    <span class="ms-auto text-secondary-light fw-normal text-sm">{{ __('merchandising.setup_products_count', ['count' => number_format($productCount)]) }}</span>
                </div>
                <div class="card-body">
                    @if (session('product_import_result'))
                        @php($result = session('product_import_result'))
                        <div class="alert alert-success radius-8 text-sm">{{ __('merchandising.setup_products_result', $result) }}</div>
                    @endif
                    <p class="text-secondary-light text-sm">{{ __('merchandising.setup_products_hint') }}</p>
                    <form method="POST" action="{{ route('merchandising.setup.products.import') }}" enctype="multipart/form-data" class="d-flex gap-2" onsubmit="this.querySelector('button').disabled = true;">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <button class="btn btn-success-600 radius-8 px-16 flex-shrink-0">{{ __('merchandising.import_button') }}</button>
                    </form>
                    <x-input-error :messages="$errors->get('file')" class="mt-1" />
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2"><i class="ri-stack-line text-primary-600"></i> {{ __('merchandising.setup_categories') }}</div>
                <div class="card-body">
                    <p class="text-secondary-light text-sm">{{ __('merchandising.setup_categories_hint') }}</p>
                    <form method="POST" action="{{ route('merchandising.setup.categories') }}">
                        @csrf @method('put')
                        @foreach ($categories as $category)
                            <div class="d-flex align-items-center gap-2 py-8 border-bottom">
                                <div class="flex-grow-1 text-sm min-w-0">
                                    <div class="fw-medium text-truncate" title="{{ $category->title }}">{{ $category->title }}</div>
                                    <div class="text-secondary-light text-xs">{{ $category->products_count }}</div>
                                </div>
                                <select name="divisions[{{ $category->id }}]" class="form-select form-select-sm w-auto">
                                    <option value="">{{ __('merchandising.setup_division_none') }}</option>
                                    @foreach (\App\Enums\MerchandisingDivision::cases() as $division)
                                        <option value="{{ $division->value }}" @selected($category->division === $division->value)>{{ $division->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                        <button class="btn btn-primary-600 radius-8 px-16 py-8 text-sm mt-16">{{ __('merchandising.save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
