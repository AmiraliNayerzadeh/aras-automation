<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('merchandising.title_new_visit') }}</h2>
            <a href="{{ route('merchandising.visits.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                <i class="ri-list-check-2"></i> {{ __('merchandising.nav_visits') }}
            </a>
        </div>
    </x-slot>

    <div id="mv-draft-banner" class="alert alert-info radius-12 d-none align-items-center justify-content-between gap-2 flex-wrap">
        <span><i class="ri-history-line"></i> {{ __('merchandising.draft_restored') }}</span>
        <button type="button" class="btn btn-sm btn-outline-secondary-600 radius-8" id="mv-draft-discard">{{ __('merchandising.draft_discard') }}</button>
    </div>

    <div id="mv-errors" class="alert alert-danger radius-12 {{ $errors->any() ? '' : 'd-none' }}" role="alert" tabindex="-1">
        <div class="fw-semibold mb-4">{{ __('merchandising.error_summary') }}</div>
        <ul class="mb-0 ps-20" id="mv-error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

    <form method="POST" action="{{ route('merchandising.visits.store') }}" id="mv-form" class="mv" novalidate>
        @csrf
        <input type="hidden" name="payload" id="mv-payload">

        {{-- 1. Visit --}}
        <section class="mv-card" id="mv-section-visit">
            <h3 class="mv-card__title"><span class="mv-step">1</span> {{ __('merchandising.section_visit') }}</h3>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="mv-label" for="mv-visited-at">{{ __('merchandising.field_visited_at') }}</label>
                    <input type="datetime-local" id="mv-visited-at" class="form-control mv-input" required>
                </div>
                <div class="col-sm-6">
                    <span class="mv-label">{{ __('merchandising.field_merchandiser') }}</span>
                    <div class="mv-static"><img src="{{ auth()->user()->avatar_url }}" alt=""> {{ auth()->user()->name }}</div>
                </div>
                <div class="col-12" id="mv-store-field">
                    <label class="mv-label" for="mv-store-search">{{ __('merchandising.field_store') }}</label>

                    <div id="mv-store-picked" class="mv-store-picked d-none">
                        <i class="ri-store-2-line"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" id="mv-store-picked-name" dir="auto"></div>
                            <div class="text-secondary-light text-xs text-truncate" id="mv-store-picked-meta" dir="auto"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary-600 radius-8" id="mv-store-change">{{ __('merchandising.store_change') }}</button>
                    </div>

                    <div id="mv-store-searchbox">
                        <div class="mv-search">
                            <i class="ri-search-line"></i>
                            <input type="search" id="mv-store-search" class="form-control mv-input" placeholder="{{ __('merchandising.store_search_placeholder') }}" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="mv-store-results">
                        </div>
                        <div id="mv-store-results" class="mv-results" role="listbox" hidden></div>
                        <button type="button" class="mv-link mt-8" id="mv-store-type-toggle">{{ __('merchandising.store_type_instead') }}</button>
                    </div>

                    <div id="mv-store-typed" class="d-none">
                        <input type="text" id="mv-store-name" class="form-control mv-input" maxlength="255" placeholder="{{ __('merchandising.store_typed_placeholder') }}">
                        <div class="text-secondary-light text-xs mt-4">{{ __('merchandising.store_typed_hint') }}</div>
                        <button type="button" class="mv-link mt-8" id="mv-store-search-toggle">{{ __('merchandising.store_back_to_search') }}</button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mv-location" id="mv-location" role="status" aria-live="polite">
                        <i class="ri-loader-4-line mv-spin" id="mv-location-icon"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold" id="mv-location-title">{{ __('merchandising.location_checking') }}</div>
                            <div class="text-xs" id="mv-location-detail"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary-600 radius-8 d-none flex-shrink-0" id="mv-location-retry">{{ __('merchandising.location_recheck') }}</button>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Photos --}}
        <section class="mv-card" id="mv-section-photos">
            <h3 class="mv-card__title"><span class="mv-step">2</span> {{ __('merchandising.section_photos') }}</h3>
            <p class="mv-hint">{{ __('merchandising.photos_required_hint') }}</p>
            <div class="row g-3">
                @foreach (['before', 'after'] as $kind)
                    <div class="col-md-6">
                        <div class="mv-label">{{ __('merchandising.photos_'.$kind) }} <span class="text-danger-600">*</span></div>
                        <div class="mv-uploads" data-kind="{{ $kind }}">
                            <label class="mv-upload-add">
                                <input type="file" accept="image/*" capture="environment" data-upload-input="{{ $kind }}">
                                <i class="ri-camera-line"></i>
                                <span>{{ __('merchandising.add_photo') }}</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 3. Products --}}
        <section class="mv-card" id="mv-section-products">
            <h3 class="mv-card__title"><span class="mv-step">3</span> {{ __('merchandising.section_products') }}</h3>
            <p class="mv-hint">{{ __('merchandising.products_hint') }}</p>

            @if (count($divisions) > 1)
                <div class="mv-segment mb-12" id="mv-division-tabs" role="tablist">
                    <button type="button" class="active" data-division="">{{ __('merchandising.products_all_lines') }}</button>
                    @foreach ($divisions as $division)
                        <button type="button" data-division="{{ $division }}">{{ __('merchandising.division_'.$division) }}</button>
                    @endforeach
                </div>
            @endif

            <div class="row g-2 mb-12">
                <div class="col-md-7">
                    <div class="mv-search">
                        <i class="ri-search-line"></i>
                        <input type="search" id="mv-product-search" class="form-control mv-input" placeholder="{{ __('merchandising.products_search_placeholder') }}" autocomplete="off" aria-label="{{ __('merchandising.products_search_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <select id="mv-line" class="form-select mv-input" aria-label="{{ __('merchandising.field_line') }}"></select>
                </div>
            </div>
            <div class="mv-catalog-count" id="mv-catalog-count" aria-live="polite"></div>
            <div class="mv-catalog" id="mv-catalog"></div>
            <button type="button" class="btn btn-outline-primary-600 radius-8 w-100 mt-12 d-none" id="mv-catalog-more"></button>

            <div class="mv-custom mt-16">
                <label class="mv-label" for="mv-custom-name">{{ __('merchandising.product_custom_add') }}</label>
                <div class="d-flex gap-2">
                    <input type="text" id="mv-custom-name" class="form-control mv-input" maxlength="255" placeholder="{{ __('merchandising.product_custom_placeholder') }}">
                    <button type="button" class="btn btn-outline-primary-600 radius-8 px-16 flex-shrink-0" id="mv-custom-add">{{ __('merchandising.product_custom_button') }}</button>
                </div>
            </div>

            <h4 class="mv-subtitle mt-24">{{ __('merchandising.products_selected') }} <span class="mv-count" id="mv-selected-count">0</span></h4>
            <div id="mv-selected"></div>
            <p class="mv-empty" id="mv-selected-empty">{{ __('merchandising.products_none_selected') }}</p>
        </section>

        {{-- 4. Actions --}}
        <section class="mv-card">
            <h3 class="mv-card__title"><span class="mv-step">4</span> {{ __('merchandising.section_actions') }}</h3>
            <div class="mv-chips" data-multi="actions">
                @foreach ($actions as $code => $label)
                    <button type="button" class="mv-chip" data-value="{{ $code }}" aria-pressed="false">{{ $label }}</button>
                @endforeach
            </div>
        </section>

        {{-- 5. Store & order --}}
        <section class="mv-card">
            <h3 class="mv-card__title"><span class="mv-step">5</span> {{ __('merchandising.section_market') }}</h3>

            <div class="mv-label">{{ __('merchandising.field_needs_order') }}</div>
            <div class="mv-segment mv-segment--narrow mb-12" id="mv-needs-order">
                <button type="button" data-value="0" class="active">{{ __('merchandising.no') }}</button>
                <button type="button" data-value="1">{{ __('merchandising.yes') }}</button>
            </div>
            <div id="mv-order-box" class="mv-order d-none">
                <div class="mv-label">{{ __('merchandising.order_pick') }}</div>
                <p class="mv-hint">{{ __('merchandising.order_hint') }}</p>
                <div class="mv-chips" id="mv-order-list"></div>
                <p class="mv-empty d-none" id="mv-order-empty">{{ __('merchandising.order_empty') }}</p>
            </div>

            <div class="mv-label mt-16">{{ __('merchandising.field_feedback') }}</div>
            <div class="mv-chips" data-multi="store_feedback">
                @foreach ($feedback as $code => $label)
                    <button type="button" class="mv-chip" data-value="{{ $code }}" aria-pressed="false">{{ $label }}</button>
                @endforeach
            </div>

            <label class="mv-label mt-16" for="mv-note">{{ __('merchandising.field_note') }}</label>
            <textarea id="mv-note" class="form-control mv-input" rows="2" maxlength="200" placeholder="{{ __('merchandising.note_placeholder') }}"></textarea>
            <div class="text-end text-secondary-light text-xs mt-4"><span id="mv-note-count">0</span>/200</div>
        </section>

        {{-- 6. More media --}}
        <section class="mv-card">
            <h3 class="mv-card__title"><span class="mv-step">6</span> {{ __('merchandising.photos_extra') }}</h3>
            <p class="mv-hint">{{ __('merchandising.photos_extra_hint') }}</p>
            <div class="mv-uploads" data-kind="extra">
                <label class="mv-upload-add">
                    <input type="file" accept="image/*,video/*" capture="environment" data-upload-input="extra">
                    <i class="ri-image-add-line"></i>
                    <span>{{ __('merchandising.add_media') }}</span>
                </label>
            </div>
        </section>

        <div class="mv-submitbar">
            <div class="mv-submitbar__status" id="mv-submit-status" aria-live="polite"></div>
            <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-12 fw-semibold" id="mv-submit">
                <i class="ri-send-plane-2-line"></i> <span>{{ __('merchandising.submit') }}</span>
            </button>
        </div>
    </form>

    @include('merchandising.visits._form_assets')
</x-app-layout>
