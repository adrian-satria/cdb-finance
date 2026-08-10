<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'primary',
    'size' => 'md',
    'icon' => '',
    'href' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'type' => 'primary',
    'size' => 'md',
    'icon' => '',
    'href' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
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
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->merge(['class' => "$classes $sizeClasses"])); ?>>
        <?php if($icon): ?> <i class="<?php echo e($icon); ?> me-1"></i> <?php endif; ?>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button <?php echo e($attributes->merge(['type' => 'submit', 'class' => "$classes $sizeClasses"])); ?>>
        <?php if($icon): ?> <i class="<?php echo e($icon); ?> me-1"></i> <?php endif; ?>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH C:\laragon\www\cdb-finance\resources\views/components/button.blade.php ENDPATH**/ ?>