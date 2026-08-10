<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'fa-solid fa-inbox',
    'title' => 'Belum Ada Data',
    'message' => '',
    'colspan' => null,
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
    'icon' => 'fa-solid fa-inbox',
    'title' => 'Belum Ada Data',
    'message' => '',
    'colspan' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($colspan): ?>
<tr>
    <td colspan="<?php echo e($colspan); ?>" class="text-center text-muted py-5">
        <i class="<?php echo e($icon); ?> d-block fs-1 mb-3 text-secondary opacity-50"></i>
        <p class="fw-semibold mb-1"><?php echo e($title); ?></p>
        <?php if($message): ?><p class="small mb-0"><?php echo $message; ?></p><?php endif; ?>
    </td>
</tr>
<?php else: ?>
<div class="text-center py-5">
    <i class="<?php echo e($icon); ?> d-block fs-1 text-muted mb-3"></i>
    <p class="fw-semibold mb-1 text-muted"><?php echo e($title); ?></p>
    <?php if($message): ?><p class="small text-muted mb-0"><?php echo $message; ?></p><?php endif; ?>
</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\cdb-finance\resources\views/components/empty-state.blade.php ENDPATH**/ ?>