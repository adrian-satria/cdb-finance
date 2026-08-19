@extends('layouts.app')
@section('title', 'Input LPJ | CDB Finance')
@section('content')
<style>.btn-add-row:hover{background:rgba(26,115,232,0.04);border-color:#1a73e8;}</style>
<div class="row">
    <div class="col-12">
        <div class="card card-bsmart">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center gap-3">
                <i class="fa-solid fa-clipboard-check fs-4 text-primary"></i>
                <h5 class="fw-bold text-dark m-0" style="font-size:20px;">LPJ Uang Muka {{ $um->no_aju }}</h5>
            </div>
            <div class="card-body p-4">
                @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if($errors->any())<div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                <div class="alert alert-info mb-3">Total UM: <strong>Rp {{ number_format($um->total_nominal, 0, ',', '.') }}</strong> &nbsp;|&nbsp; Sisa LPJ: <strong>Rp {{ number_format($um->sisa_lpj, 0, ',', '.') }}</strong>. Isi realisasi penggunaan dana.</div>

                <form action="/uang-muka/lpj/simpan" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="no_aju" value="{{ $um->no_aju }}">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label-custom">Tanggal LPJ</label>
                            <input type="date" name="tanggal" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark m-0" style="font-size:15px;"><i class="fa-solid fa-list-ol text-primary me-2"></i>Rincian Realisasi</h6>
                        <button type="button" class="btn-add-row" onclick="tambahBaris()"><i class="fa-solid fa-plus me-1"></i> Tambah Baris</button>
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table align-middle table-bsmart" id="detailTable">
                            <thead><tr><th width="4%" class="text-center">No</th><th width="12%">TANGGAL</th><th width="30%">Uraian</th><th width="16%">Kode Budget</th><th width="16%">Realisasi (Rp)</th><th width="12%">No Bukti</th><th width="5%" class="text-center">Aksi</th></tr></thead>
                            <tbody>
                                <tr style="border-bottom:1px solid #f1f3f4;">
                                    <td class="text-center row-number fw-semibold text-secondary">1</td>
                                    <td><input type="date" name="items[0][tanggal]" class="form-control form-control-custom"></td>
                                    <td><input type="text" name="items[0][keterangan]" class="form-control form-control-custom" placeholder="Rincian realisasi..." required></td>
                                    <td><input type="text" name="items[0][kode_budget]" class="form-control-custom" placeholder="Kode Budget" required></td>
                                    <td><div class="input-group input-group-sm"><span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px;color:#6b7280;">Rp</span><input type="text" name="items[0][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotal()"></div></td>
                                    <td><input type="text" name="items[0][no_bukti]" class="form-control form-control-custom" placeholder="hal. bukti"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBaris(this)"><i class="fa-solid fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mb-4">
                        <div class="total-box d-flex align-items-center gap-3"><span class="text-secondary small fw-normal text-uppercase">Total Realisasi:</span><span id="totalText" class="text-danger fs-5 fw-bold">Rp 0</span></div>
                    </div>

                    <div class="text-end">
                        <a href="/uang-muka/{{ $um->no_aju }}" class="btn-bsmart-secondary">Batal</a>
                        <button type="submit" class="btn-bsmart-primary"><i class="fa-solid fa-paper-plane me-1"></i> Kirim LPJ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
let rowIndex = 1;
function tambahBaris(){const b=document.querySelector('#detailTable tbody');const tr=document.createElement('tr');tr.style.borderBottom='1px solid #f1f3f4';tr.innerHTML=`<td class="text-center row-number fw-semibold text-secondary">${b.children.length+1}</td><td><input type="date" name="items[${rowIndex}][tanggal]" class="form-control form-control-custom"></td><td><input type="text" name="items[${rowIndex}][keterangan]" class="form-control form-control-custom" placeholder="Rincian realisasi..." required></td><td><input type="text" name="items[${rowIndex}][kode_budget]" class="form-control-custom" placeholder="Kode Budget" required></td><td><div class="input-group input-group-sm"><span class="input-group-text bg-transparent border-end-0 px-2" style="font-size:12px;color:#6b7280;">Rp</span><input type="text" name="items[${rowIndex}][jumlah]" class="form-control form-control-custom input-jumlah" placeholder="0" required inputmode="numeric" oninput="formatNominal(this); hitungTotal()"></div></td><td><input type="text" name="items[${rowIndex}][no_bukti]" class="form-control form-control-custom" placeholder="hal. bukti"></td><td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBaris(this)"><i class="fa-solid fa-trash"></i></button></td>`;b.appendChild(tr);rowIndex++;}
function hapusBaris(b){const body=document.querySelector('#detailTable tbody');if(body.children.length>1){b.closest('tr').remove();document.querySelectorAll('.row-number').forEach((td,i)=>td.innerText=i+1);hitungTotal();}else alert('Minimal 1 item!');}
function formatNominal(i){let r=i.value.replace(/[^0-9]/g,'');i.value=r===''?'':parseInt(r,10).toLocaleString('id-ID');}
function hitungTotal(){let t=0;document.querySelectorAll('.input-jumlah').forEach(i=>t+=parseInt(i.value.replace(/[^0-9]/g,''))||0);document.getElementById('totalText').innerText='Rp '+new Intl.NumberFormat('id-ID').format(t);}
document.querySelector('form')?.addEventListener('submit',function(){document.querySelectorAll('.input-jumlah').forEach(i=>i.value=i.value.replace(/[^0-9]/g,''));});
</script>
@endsection
