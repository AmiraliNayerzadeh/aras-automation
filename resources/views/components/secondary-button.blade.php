<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-secondary-600 radius-8 px-20 py-11']) }}>
    {{ $slot }}
</button>
