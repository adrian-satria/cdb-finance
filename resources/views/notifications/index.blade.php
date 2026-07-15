@extends('layouts.app')
@section('title', 'Notifikasi | B-SMART')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-bell text-warning me-2"></i>Notifikasi Saya</h4>
        <form action="{{ route('notifications.readAll') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-check-double me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            @forelse($notifications as $n)
            <div class="p-4 border-bottom {{ $n->is_read ? '' : 'bg-primary-subtle bg-opacity-10' }}" style="border-color: #f1f3f4;">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            @if($n->type === 'pending_approval')
                                <span class="badge bg-warning p-2 rounded-circle"><i class="fa-solid fa-clock text-white"></i></span>
                            @elseif($n->type === 'approved')
                                <span class="badge bg-success p-2 rounded-circle"><i class="fa-solid fa-check text-white"></i></span>
                            @elseif($n->type === 'rejected' || $n->type === 'revised')
                                <span class="badge bg-danger p-2 rounded-circle"><i class="fa-solid fa-xmark text-white"></i></span>
                            @elseif($n->type === 'disbursed')
                                <span class="badge bg-info p-2 rounded-circle"><i class="fa-solid fa-money-bill-wave text-white"></i></span>
                            @else
                                <span class="badge bg-secondary p-2 rounded-circle"><i class="fa-solid fa-bell text-white"></i></span>
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold text-dark {{ $n->is_read ? '' : '' }}">{{ $n->title }}</div>
                            <div class="text-muted small">{{ $n->message }}</div>
                            <div class="text-muted mt-1" style="font-size: 11px;">
                                <i class="fa-regular fa-clock me-1"></i>{{ $n->created_at->diffForHumans() }}
                                @if(!$n->is_read)
                                    <span class="badge bg-primary ms-2" style="font-size: 8px;">Baru</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if($n->reference_type === 'SPP' && $n->reference_id)
                            <a href="/spp" class="btn btn-sm btn-outline-primary">Lihat</a>
                        @endif
                        @if(!$n->is_read)
                        <form action="{{ route('notifications.read', $n->id_notifikasi) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Tandai dibaca">
                                <i class="fa-regular fa-circle-check"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fa-regular fa-bell-slash d-block fs-1 text-muted mb-3"></i>
                <p class="text-muted">Tidak ada notifikasi.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
