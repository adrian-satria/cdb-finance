export function showDetailSpp(noSurat) {
    const detailTitle = document.getElementById('detailNoSurat');
    const loadingRow = document.getElementById('loadingRow');
    const detailBody = document.getElementById('detailItemsBody');
    const detailTotal = document.getElementById('detailTotalNominal');
    if (!detailBody || !detailTotal) return;

    if (detailTitle) detailTitle.innerText = "Rincian Item: " + noSurat;
    if (loadingRow) loadingRow.style.display = 'table-row-group';
    detailBody.innerHTML = '';
    detailTotal.innerText = 'Rp 0';

    const modal = document.getElementById('modalDetailSpp');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    fetch('/spp/detail-items?no_surat=' + encodeURIComponent(noSurat))
        .then(r => r.json())
        .then(data => {
            if (loadingRow) loadingRow.style.display = 'none';
            let html = '';
            let total = 0;
            if (data.length === 0) {
                html = '<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada item rincian dana untuk surat ini.</td></tr>';
            } else {
                data.forEach((item, i) => {
                    const nominal = parseFloat(item.nominal ?? 0);
                    total += nominal;
                    html += '<tr style="border-bottom:1px solid #f1f3f4;">'
                        + '<td class="text-center text-secondary fw-semibold py-2.5">' + (i + 1) + '</td>'
                        + '<td><span class="fw-semibold text-dark d-block">' + (item.kode_budget || '') + '</span>'
                        + '<small class="text-muted" style="font-size:11px;">' + (item.nama_budget ?? 'Komponen Anggaran') + '</small></td>'
                        + '<td class="text-secondary">' + (item.keterangan ?? '-') + '</td>'
                        + '<td class="text-end fw-bold text-dark">Rp ' + nominal.toLocaleString('id-ID') + '</td></tr>';
                });
            }
            detailBody.innerHTML = html;
            detailTotal.innerText = 'Rp ' + total.toLocaleString('id-ID');
        })
        .catch(() => {
            if (loadingRow) loadingRow.style.display = 'none';
            detailBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Gagal memuat rincian data dari server.</td></tr>';
        });
}

export function showPreviewSpp(noSurat) {
    const label = document.getElementById('previewSuratLabel');
    const frame = document.getElementById('previewFrame');
    const printLink = document.getElementById('previewPrintLink');
    const loading = document.getElementById('previewLoading');

    if (label) label.innerText = "No. Surat: " + noSurat;
    if (frame) frame.src = '/spp/preview-cetak?no_surat=' + encodeURIComponent(noSurat);
    if (printLink) printLink.href = '/spp/cetak?no_surat=' + encodeURIComponent(noSurat);
    if (loading) loading.style.display = 'flex';

    if (frame) {
        let loaded = false;
        frame.onload = function() {
            if (loading) loading.style.display = 'none';
            loaded = true;
        };
        setTimeout(() => {
            if (!loaded && loading) {
                loading.innerHTML = '<div class="text-center text-danger"><i class="fa-solid fa-triangle-exclamation fs-3 mb-2"></i><div>Gagal memuat preview.</div></div>';
            }
        }, 15000);
    }

    const modal = document.getElementById('modalPreviewSpp');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

export function initFormLoading() {
    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('[type="submit"]');
            if (btn) {
                const orig = btn.innerHTML;
                btn.dataset.origHtml = orig;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Memproses...';
                btn.disabled = true;
            }
        });
    });
}

document.addEventListener('click', function(e) {
    var btn = e.target.closest('[data-action="detail-spp"]');
    if (btn) {
        e.preventDefault();
        showDetailSpp(btn.getAttribute('data-no-surat'));
        return;
    }

    btn = e.target.closest('[data-action="preview-spp"]');
    if (btn) {
        showPreviewSpp(btn.getAttribute('data-no-surat'));
        return;
    }
});
