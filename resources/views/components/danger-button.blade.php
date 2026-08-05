<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger-600 radius-8 px-20 py-11']) }}>
    {{ $slot }}
</button>
