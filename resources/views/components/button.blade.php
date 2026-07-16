@props([
    'type' => 'primary',
    'size' => 'md',
    'icon' => '',
    'href' => null,
])

@php
    $classes = match($type) {
        'primary' => 'btn btn-primary fw-semibold',
        'secondary' => 'btn btn-light fw-semibold text-secondary',
        'danger' => 'btn btn-danger fw-semibold',
        'warning' => 'btn btn-warning fw-semibold',
        'outline-primary' => 'btn btn-outline-primary fw-semibold',
        'outline-danger' => 'btn btn-outline-danger fw-semibold',
        'outline-warning' => 'btn btn-outline-warning fw-semibold',
        'outline-info' => 'btn btn-outline-info fw-semibold',
        default => 'btn btn-primary fw-semibold',
    };

    $sizeClasses = match($size) {
        'sm' => 'btn-sm px-3',
        'md' => 'px-4',
        'lg' => 'btn-lg px-5',
        default => 'px-4',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$classes $sizeClasses"]) }}>
        @if($icon) <i class="{{ $icon }} me-1"></i> @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => "$classes $sizeClasses"]) }}>
        @if($icon) <i class="{{ $icon }} me-1"></i> @endif
        {{ $slot }}
    </button>
@endif
