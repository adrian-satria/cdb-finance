<div class="modal fade" id="modalValidasi" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-shield-check text-primary me-2"></i>Otorisasi Pengajuan SPP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formValidasi" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4">
                    <div class="p-3 bg-light rounded-3 mb-3" style="font-size: 13px;">
                        <div class="row mb-1">
                            <div class="col-4 text-secondary">No. Surat</div>
                            <div class="col-8 fw-bold text-dark" id="modalNoSurat"></div>
                        </div>
                        <div class="row">
                            <div class="col-4 text-secondary">Total Dana</div>
                            <div class="col-8 fw-bold text-primary" id="modalTotalNominal"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Keputusan Otorisasi</label>
                        <select name="aksi" class="form-select" id="selectAksi" required onchange="cekKewajibanAlasan()">
                            <option value="approve">🟢 Setujui Pengajuan (Approve)</option>
                            <option value="revise">🟡 Revisi ke Maker (Tambah/Perbaiki Lampiran)</option>
                            <option value="reject">🔴 Tolak Pengajuan (Buat Ulang SPP Baru)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small" id="labelAlasan">Catatan / Alasan Kelayakan</label>
                        <textarea name="alasan" id="textAlasan" class="form-control" rows="3" placeholder="Masukkan alasan jika menolak, atau catatan opsional..."></textarea>
                    </div>
                    <div class="alert alert-info small mt-2" role="alert" style="font-size: 12px;">
                        <strong>Catatan:</strong> Pilih <em>Revisi</em> jika SPP perlu dikembalikan ke Maker untuk menambahkan lampiran atau memperbaiki dokumen. Pilih <em>Tolak</em> jika pengajuan harus dibatalkan dan dibuat ulang.
                    </div>

                    <div class="mb-2 mt-3">
                        <label class="form-label fw-semibold text-secondary small">Lampiran Berkas Pendukung (Bisa Banyak File)</label>
                        <input type="file" name="file_checker[]" class="form-control" multiple style="font-size: 13px; border-radius: 12px; padding: 8px 12px; border-color: #e1e3e5;">
                        <span class="text-muted d-block mt-1" style="font-size: 11px;">* Format: PDF, PNG, JPG (Maks 5MB per file sesuai standar korporat).</span>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill fw-semibold text-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold">Proses Keputusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailSpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0" id="detailNoSurat">Rincian Pengajuan</h5>
                    <p class="text-muted small m-0 mt-1">Daftar item alokasi anggaran yang diajukan oleh unit kerja.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive bg-light p-2 rounded-4 border">
                    <table class="table table-borderless align-middle m-0">
                        <thead class="text-secondary small fw-bold" style="font-size: 12px; border-bottom: 2px solid #e1e3e5;">
                            <tr>
                                <th width="8%" class="text-center py-2">No</th>
                                <th width="22%" class="py-2">Kode Budget</th>
                                <th width="45%" class="py-2">Keterangan Kegiatan</th>
                                <th width="25%" class="text-end py-2">Nominal Dana</th>
                            </tr>
                        </thead>
                        <tbody id="loadingRow">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Mengambil rincian berkas dari server...
                                </td>
                            </tr>
                        </tbody>
                        <tbody id="detailItemsBody" style="font-size: 13px;"></tbody>
                        <tfoot style="border-top: 2px solid #e1e3e5; font-weight: 700;">
                            <tr>
                                <td colspan="3" class="text-end text-secondary py-3">Total Pengajuan:</td>
                                <td id="detailTotalNominal" class="text-end text-primary py-3" style="font-size: 15px;">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal" style="font-size: 13px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreviewSpp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content border-0" style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0">Preview Hasil Cetak SPP</h5>
                    <p class="text-muted small m-0 mt-1" id="previewSuratLabel">Menunggu nomor surat...</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 72vh;">
                <div id="previewFrameWrapper" class="position-relative" style="min-height: 72vh;">
                    <div id="previewLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white" style="z-index: 10;">
                        <div class="text-center text-secondary">
                            <i class="fa-solid fa-spinner fa-spin fs-3 mb-2"></i>
                            <div>Memuat preview SPP dari server...</div>
                        </div>
                    </div>
                    <iframe id="previewFrame" src="" frameborder="0" class="w-100 h-100" style="min-height: 72vh;"></iframe>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal" style="font-size: 13px;">Tutup</button>
                <a id="previewPrintLink" href="#" target="_blank" class="btn btn-primary px-4 rounded-pill fw-semibold" style="font-size: 13px;">
                    <i class="fa-solid fa-print me-2"></i> Cetak PDF
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bukaModalValidasi(id, noSurat, nominal) {
        document.getElementById('formValidasi').action = '/spp/validasi?no_surat=' + encodeURIComponent(id);
        document.getElementById('modalNoSurat').innerText = noSurat;
        document.getElementById('modalTotalNominal').innerText = 'Rp ' + nominal;

        document.getElementById('selectAksi').value = 'approve';
        document.getElementById('textAlasan').required = false;
        document.getElementById('textAlasan').placeholder = "Masukkan alasan jika menolak, atau catatan opsional...";
        document.getElementById('labelAlasan').innerText = "Catatan / Alasan Kelayakan";

        var myModal = new bootstrap.Modal(document.getElementById('modalValidasi'));
        myModal.show();
    }

    function cekKewajibanAlasan() {
        const aksi = document.getElementById('selectAksi').value;
        const textAlasan = document.getElementById('textAlasan');
        const labelAlasan = document.getElementById('labelAlasan');

        if (aksi === 'reject') {
            textAlasan.required = true;
            textAlasan.placeholder = "WAJIB DIISI! Berikan kronologi/alasan penolakan berkas secara gamblang...";
            labelAlasan.innerHTML = 'Catatan / Alasan Kelayakan <span class="text-danger">*Wajib</span>';
        } else {
            textAlasan.required = false;
            textAlasan.placeholder = "Masukkan alasan jika menolak, atau catatan opsional...";
            labelAlasan.innerText = "Catatan / Alasan Kelayakan";
        }
    }
</script>
@endpush
