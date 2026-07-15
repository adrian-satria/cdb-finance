@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #fde8e8; color: #9b1c1c;" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-triangle-exertion fs-5 me-3"></i>
            <div>{!! session('error') !!}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="/spp/simpan" method="POST">
    @csrf <div class="card">
        <div class="card-header">Input Surat Permintaan</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Nomor Surat</label>
                    <input type="text" name="no_surat" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div class="mt-3">
                <label>Penerima (Nama Rekening)</label>
                <input type="text" name="nama_rekening_tujuan" class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary mt-4">Simpan Pengajuan</button>
        </div>
    </div>
</form>