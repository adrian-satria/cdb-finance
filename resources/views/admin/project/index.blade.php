@extends('layouts.app')

@section('title', 'Master Project | Finance Management Demo')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary m-0"><i class="fa-solid fa-diagram-project me-2"></i>Master Data Project</h4>
        <a href="{{ route('admin.project.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-plus me-1"></i> Tambah Project Baru
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
                            <th width="12%" class="text-center border-0 py-3">Kode Project</th>
                            <th width="30%" class="border-0 py-3">Nama Project</th>
                            <th width="35%" class="border-0 py-3">Area Kerja</th>
                            <th width="8%" class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $index => $p)
                        <tr style="border-bottom: 1px solid #f1f3f4;">
                            <td class="text-center fw-semibold text-secondary py-3">{{ $projects->firstItem() + $index }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $p->kode_project }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $p->nama_project }}</td>
                            <td>
                                @foreach($p->areas as $a)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border me-1 mb-1">
                                        {{ $a->kode_area }} - {{ $a->nama_area }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.project.edit', $p->kode_project) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.project.destroy', $p->kode_project) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus project {{ $p->kode_project }}?');" class="d-inline">
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
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada data project.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $projects->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

