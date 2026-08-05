@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1'])

@php
$menuAlignClass = $align === 'left' ? '' : 'dropdown-menu-end';
@endphp

<div class="dropdown">
    <div data-bs-toggle="dropdown" aria-expanded="false" role="button">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu {{ $menuAlignClass }} {{ $contentClasses }} shadow-sm">
        {{ $content }}
    </div>
</div>
