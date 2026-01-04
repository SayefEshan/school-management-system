@props(['value'])

<label {{ $attributes->merge(['class' => 'col-form-label']) }}>
    {{ $value ?? $slot }}
</label>
