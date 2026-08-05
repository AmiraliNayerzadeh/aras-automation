<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-dark btn-sm']) }}>
    {{ $slot }}
</button>
