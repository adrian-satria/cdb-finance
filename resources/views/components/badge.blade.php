@props([
    'type' => 'secondary',
])

@php
    $class = match($type) {
        'success' => 'badge bg-success',
        'warning' => 'badge bg-warning text-dark',
        'danger' => 'badge bg-danger',
        'info' => 'badge bg-info text-dark',
        'primary' => 'badge bg-primary',
        'secondary' => 'badge bg-secondary',
        'light' => 'badge bg-light text-dark border',
        default => 'badge bg-secondary',
    };
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</span>
