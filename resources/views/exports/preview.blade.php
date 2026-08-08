<x-app-layout>
    <x-slot name="header">
        <div class="no-print">
            <h2 class="h4 fw-semibold mb-0">{{ __('exports.title') }} — {{ $title }}</h2>
        </div>
    </x-slot>

    <div class="card radius-12 mb-24 no-print">
        <div class="card-body">
            <form method="GET" class="d-flex flex-wrap gap-16 align-items-center">
                @foreach (request()->except(['with_header', 'with_footer', 'with_date', 'with_user']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="form-check">
                    <input type="hidden" name="with_header" value="0">
                    <input type="checkbox" class="form-check-input" id="with_header" name="with_header" value="1" onchange="this.form.submit()" @checked($withHeader)>
                    <label class="form-check-label" for="with_header">{{ __('exports.with_header') }}</label>
                </div>
                <div class="form-check">
                    <input type="hidden" name="with_footer" value="0">
                    <input type="checkbox" class="form-check-input" id="with_footer" name="with_footer" value="1" onchange="this.form.submit()" @checked($withFooter)>
                    <label class="form-check-label" for="with_footer">{{ __('exports.with_footer') }}</label>
                </div>
                <div class="form-check">
                    <input type="hidden" name="with_date" value="0">
                    <input type="checkbox" class="form-check-input" id="with_date" name="with_date" value="1" onchange="this.form.submit()" @checked($withDate)>
                    <label class="form-check-label" for="with_date">{{ __('exports.with_date') }}</label>
                </div>
                <div class="form-check">
                    <input type="hidden" name="with_user" value="0">
                    <input type="checkbox" class="form-check-input" id="with_user" name="with_user" value="1" onchange="this.form.submit()" @checked($withUser)>
                    <label class="form-check-label" for="with_user">{{ __('exports.with_user') }}</label>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <a href="{{ $pdfUrl }}" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-file-pdf-2-line"></i> {{ __('exports.download_pdf') }}
                    </a>
                    <a href="{{ $excelUrl }}" class="btn btn-outline-success-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-file-excel-2-line"></i> {{ __('exports.download_excel') }}
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-printer-line"></i> {{ __('exports.print') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card radius-12 printable-area">
        <div class="card-body">
            @include('exports._header', ['withHeader' => $withHeader, 'logoSrc' => asset('logo.png'), 'title' => $title])
            @include('exports._meta', ['withDate' => $withDate, 'withUser' => $withUser])

            @include($contentView, $contentData)

            @include('exports._footer', ['withFooter' => $withFooter])
        </div>
    </div>

    <style>
        @media print {
            .no-print, .sidebar, .navbar-header { display: none !important; }
            .dashboard-main { margin: 0 !important; padding: 0 !important; }
            .printable-area { border: none !important; box-shadow: none !important; }
        }
    </style>
</x-app-layout>
