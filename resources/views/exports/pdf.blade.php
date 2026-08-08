<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['fa'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: {{ in_array(app()->getLocale(), ['fa'], true) ? 'right' : 'left' }}; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('exports._header', ['withHeader' => $withHeader, 'logoSrc' => public_path('logo.png'), 'title' => $title])
    @include('exports._meta', ['withDate' => $withDate, 'withUser' => $withUser])

    @include($contentView, $contentData)

    @include('exports._footer', ['withFooter' => $withFooter])
</body>
</html>
