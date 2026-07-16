@props([
    'title' => '',
    'icon' => '',
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm mb-4 ' . $class]) }} style="border-radius: 16px;">
    @if($title || $icon || $slot->isNotEmpty())
    <div class="card-header bg-transparent border-0 d-flex align-items-center gap-2 px-4 pt-4 pb-0">
        @if($icon)
            <i class="{{ $icon }} text-primary fs-5"></i>
        @endif
        @if($title)
            <h5 class="fw-bold text-dark m-0">{{ $title }}</h5>
        @endif
    </div>
    @endif
    <div class="card-body p-4">
        {{ $slot }}
    </div>
</div>
