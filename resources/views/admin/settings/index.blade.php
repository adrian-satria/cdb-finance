@extends('layouts.app')
@section('title', 'Pengaturan Sistem | Finance Management Demo')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0"><i class="fa-solid fa-gear text-secondary me-2"></i>Pengaturan Sistem</h4>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.settings.create-default') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Default Settings
                </button>
            </form>
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm">
                    <i class="fa-solid fa-rotate me-1"></i> Clear Cache
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="card card-bsmart">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle text-13">
                    <thead class="bg-table-header">
                        <tr>
                            <th class="border-0 py-3">Group</th>
                            <th class="border-0 py-3">Label</th>
                            <th class="border-0 py-3">Key</th>
                            <th class="border-0 py-3">Value</th>
                            <th class="text-center border-0 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $s)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $s->group }}</span></td>
                            <td class="fw-semibold">{{ $s->label }}</td>
                            <td><code>{{ $s->key }}</code></td>
                            <td>
                                <form action="{{ route('admin.settings.update') }}" method="POST" class="row g-1">
                                    @csrf
                                    <input type="hidden" name="key" value="{{ $s->key }}">
                                    <div class="col-8">
                                        @if($s->type === 'boolean')
                                            <select name="value" class="form-select form-select-sm">
                                                <option value="true" {{ $s->value === 'true' ? 'selected' : '' }}>Aktif</option>
                                                <option value="false" {{ $s->value === 'false' ? 'selected' : '' }}>Nonaktif</option>
                                            </select>
                                        @else
                                            <input type="{{ $s->type === 'number' ? 'number' : 'text' }}" name="value" value="{{ $s->value }}" class="form-control form-control-sm">
                                        @endif
                                    </div>
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Simpan</button>
                                    </div>
                                </form>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small">{{ $s->type }}</span>
                            </td>
                        </tr>
                        @empty
                        <x-empty-state colspan="5" title="Belum Ada Pengaturan" message="Klik &lt;strong&gt;Default Settings&lt;/strong&gt; untuk membuat pengaturan awal." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">{{ $settings->links() }}</div>
        </div>
    </div>
</div>
@endsection

