@props([
    'icon' => 'fa-solid fa-inbox',
    'title' => 'Belum Ada Data',
    'message' => '',
    'colspan' => null,
])

@if($colspan)
<tr>
    <td colspan="{{ $colspan }}" class="text-center text-muted py-5">
        <i class="{{ $icon }} d-block fs-1 mb-3 text-secondary opacity-50"></i>
        <p class="fw-semibold mb-1">{{ $title }}</p>
        @if($message)<p class="small mb-0">{!! $message !!}</p>@endif
    </td>
</tr>
@else
<div class="text-center py-5">
    <i class="{{ $icon }} d-block fs-1 text-muted mb-3"></i>
    <p class="fw-semibold mb-1 text-muted">{{ $title }}</p>
    @if($message)<p class="small text-muted mb-0">{!! $message !!}</p>@endif
</div>
@endif
