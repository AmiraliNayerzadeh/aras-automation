@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
$maxWidthClass = [
    'sm' => 'modal-sm',
    'md' => '',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    '2xl' => 'modal-xl',
][$maxWidth] ?? '';
@endphp

<div class="modal fade" id="modal-{{ $name }}" data-modal-name="{{ $name }}" tabindex="-1" aria-hidden="true" data-bs-show="{{ $show ? '1' : '0' }}">
    <div class="modal-dialog {{ $maxWidthClass }} modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
