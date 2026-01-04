<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary my-1 me-2', 'id' => 'submit-button']) }}>
    {{ $slot }}
</button>
