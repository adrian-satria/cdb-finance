@extends('layouts.app')

@section('title', 'Master Area | Finance Management Demo')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-location-dot me-2"></i>Master Data Area</h4>
        <a href="{{ route('admin.area.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-plus me-1"></i> Tambah Area Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle m-0 table-bsmart">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center border-0 py-3">No</th>
                            <th width="15%" class="text-center border-0 py-3">Kode Area</th>
                            <th width="40%" class="border-0 py-3">Nama Area</th>
                            <th width="30%" class="border-0 py-3">Project Terkait</th>
                            <th width="8%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areas as $index => $a)
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td class="text-center fw-semibold text-secondary py-3">{{ $areas->firstItem() + $index }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $a->kode_area }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $a->nama_area }}</td>
                            <td>
                                @forelse($a->projects as $p)
                                    <span class="badge bg-info bg-opacity-10 text-info border me-1 mb-1">
                                        {{ $p->kode_project }} - {{ $p->nama_project }}
                                    </span>
                                @empty
                                    <span class="text-muted small">Belum ada project</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.area.edit', $a->kode_area) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.area.destroy', $a->kode_area) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus area {{ $a->kode_area }}?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada data area.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $areas->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

