<div class="table-responsive">
    <table class="table align-middle m-0 table-bsmart">
        <thead>
            <tr>
                <th width="4%" class="text-center border-0 py-3">No</th>
                <th width="8%" class="text-center border-0 py-3">Project</th>
                <th width="10%" class="text-center border-0 py-3">Kode Budget</th>
                <th width="33%" class="border-0 py-3">Nama Komponen Anggaran</th>
                <th width="13%" class="text-end border-0 py-3">Alokasi Pagu (Budget)</th>
                <th width="12%" class="text-end border-0 py-3">Terserap (Actual)</th>
                <th width="12%" class="text-end border-0 py-3">Sisa Saldo</th>
                <th width="8%" class="text-center border-0 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody id="budgetTableBody">
            @forelse($budgets as $index => $b)
            @php
                $alokasi = floatval($b->alokasi_dana ?? 0);
                $terserap = floatval($b->terserap ?? 0);
                $sisa_saldo = $alokasi - $terserap;
            @endphp
            <tr style="border-bottom: 1px solid #f1f3f4;" class="hover-actions">
                <td class="text-center fw-semibold text-secondary py-3">{{ $budgets->firstItem() + $index }}</td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border badge-custom">{{ $b->kode_project }}</span>
                </td>
                <td class="text-center fw-bold text-primary"><code>{{ $b->kode_budget }}</code></td>
                <td class="py-3 pr-3">
                    <div class="fw-semibold text-dark mb-0.5" style="line-height: 1.4;">{{ $b->nama_budget }}</div>
                </td>
                <td class="text-end fw-semibold text-dark">Rp {{ number_format($alokasi, 0, ',', '.') }}</td>
                <td class="text-end fw-semibold text-secondary">Rp {{ number_format($terserap, 0, ',', '.') }}</td>
                <td class="text-end fw-bold {{ $sisa_saldo <= 0 ? 'text-danger' : ($sisa_saldo < 2000000 ? 'text-warning' : 'text-success') }}">
                    Rp {{ number_format($sisa_saldo, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <a href="{{ route('admin.budget.area', $b->id_budget) }}" class="btn btn-sm btn-outline-info btn-icon-circle" title="Atur per Area"><i class="fa-solid fa-location-dot"></i></a>
                        <a href="{{ route('admin.budget.edit', $b->id_budget) }}" class="btn btn-sm btn-outline-warning btn-icon-circle" title="Edit Budget"><i class="fa-regular fa-pen-to-square"></i></a>
                        <form action="{{ route('admin.budget.destroy', $b->id_budget) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus budget {{ $b->kode_budget }} ?');" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon-circle" title="Hapus Budget"><i class="fa-regular fa-trash-can"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            @if($kodeProject)
                <tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada data budget untuk project yang dipilih.</td></tr>
            @else
                <tr><td colspan="8" class="text-center py-5 text-muted">Silakan pilih project terlebih dahulu.</td></tr>
            @endif
            @endforelse
        </tbody>
    </table>
</div>

@if(count($budgets) > 0)
<div class="d-flex justify-content-center mt-4">
    {{ $budgets->appends(request()->query())->links() }}
</div>
@endif
