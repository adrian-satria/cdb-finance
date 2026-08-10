<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Permintaan Pembayaran - {{ $surat->no_surat }}</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 12px; 
            color: #333; 
            line-height: 1.4; 
        }
        
        /* Layout Header Atas dari Kode Terbaru (Kiri & Kanan Sejajar) */
        .header-container { 
            width: 100%; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .header-left { 
            width: 55%; 
            vertical-align: top; 
        }
        .header-right { 
            width: 45%; 
            vertical-align: top; 
        }
        .institution-name { 
            font-size: 14px; 
            font-weight: bold; 
            text-transform: uppercase;
        }
        .institution-address { 
            font-size: 11px; 
            color: #555; 
            margin-top: 3px;
        }
        
        /* Judul Dokumen Utama */
        .doc-title { 
            text-align: center; 
            font-size: 16px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-top: 5px;
            margin-bottom: 15px; 
        }

        /* Pembungkus Utama (Box Border Sesuai Lembar Fisik) */
        .main-box {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        /* Tabel Sub-Section Dalam Box */
        .section-table {
            width: 100%;
            border-collapse: collapse;
        }
        .section-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11.5px;
        }
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            padding-left: 6px;
            padding-top: 6px;
            font-size: 11.5px;
        }
        .border-bottom {
            border-bottom: 1px solid #000;
        }

        /* === FORMAT TABEL RINCIAN DARI KODE LAMA === */
        .content-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 0px; 
        }
        .content-table th { 
            background-color: #f2f2f2; 
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 8px; 
            font-weight: bold; 
            text-align: left; 
            font-size: 11px; 
        }
        .content-table th:last-child {
            border-right: none;
        }
        .content-table td { 
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #000;
            padding: 8px; 
            vertical-align: top; 
        }
        .content-table td:last-child {
            border-right: none;
        }
        .total-row { 
            font-weight: bold; 
            background-color: #f9f9f9; 
        }
        
        /* Utilities Alignment */
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        /* Modul Tambahan Teks Terbilang di bawah Tabel */
        .terbilang-box {
            padding: 10px 8px;
            font-style: italic;
            font-weight: bold;
            font-size: 11.5px;
            background-color: #fefefe;
        }

        /* === FORMAT TANDA TANGAN === */
        .ttd-table { 
            width: 100%; 
            margin-top: 30px; 
            border-collapse: collapse; 
        }
        .ttd-cell { 
            text-align: center; 
            vertical-align: top; 
            height: 120px; 
            font-size: 11px; 
        }
        .signature-img { 
            height: 55px; 
            margin: 5px 0; 
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .ttd-label { font-weight: bold; font-size: 11px; margin-bottom: 2px; }
        .ttd-role { font-size: 9px; color: #666; }
    </style>
</head>
<body>

    <table class="header-container">
        <tr>
            <td class="header-left">
                <div class="institution-name">UPKM/CD Bethesda YAKKUM</div>
                <div class="institution-address">
                    Klitren Lor GK 3 No.374<br>
                    Yogyakarta
                </div>
            </td>
            <td class="header-right">
                <table style="width: 100%; font-size: 11.5px;">
                    <tr>
                        <td width="30%"><strong>No. Surat</strong></td>
                        <td width="5%">:</td>
                        <td width="65%">{{ $surat->no_surat }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal</strong></td>
                        <td>:</td>
                        <td>{{ date('d F Y', strtotime($surat->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kode Project</strong></td>
                        <td>:</td>
                        <td>{{ $surat->kode_project }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="doc-title">Surat Permintaan Pembayaran (SPP)</div>

    <table class="main-box">
        <tr>
            <td class="section-title">Sumber Dana</td>
        </tr>
        <tr>
            <td class="border-bottom" style="padding-bottom: 6px;">
                <table class="section-table">
                    <tr>
                        <td width="25%">Sumber Dana</td>
                        <td width="2%">:</td>
                        <td width="73%">{{ $surat->sumber_dana ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="section-title">Tujuan Pembayaran</td>
        </tr>
        <tr>
            <td class="border-bottom" style="padding-bottom: 6px;">
                <table class="section-table">
                    <tr>
                        <td width="25%">Nama Rekening</td>
                        <td width="2%">:</td>
                        <td width="73%">{{ $surat->nama_rekening_tujuan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Bank / No. Rekening</td>
                        <td>:</td>
                        <td>{{ $surat->bank_tujuan ?? '-' }} / {{ $surat->no_rekening_tujuan ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding: 0;">
                <table class="content-table border-bottom">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="15%" class="text-center">Kode Budget</th>
                            <th width="55%">Keterangan Kegiatan / Komponen Anggaran</th>
                            <th width="25%" class="text-end">Nominal Dana</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center"><code>{{ $item->kode_budget }}</code></td>
                            <td>
                                <strong>{{ $item->nama_budget ?? 'Komponen Anggaran' }}</strong><br>
                                <span style="font-size: 10.5px; color:#555;">{{ $item->keterangan }}</span>
                            </td>
                            <td class="text-end" style="font-weight: bold;">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="3" class="text-end">Total Keseluruhan Pengajuan:</td>
                            <td class="text-end" style="color: #0369a1;">Rp {{ number_format($surat->total_nominal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        <tr>
            <td class="terbilang-box">
                @php
                    if (!function_exists('terbilang')) { function terbilang($angka) {
                        $angka = abs($angka);
                        $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
                        $terbilang = "";
                        if ($angka < 12) { $terbilang = " " . $baca[$angka]; }
                        elseif ($angka < 20) { $terbilang = terbilang($angka - 10) . " belas"; }
                        elseif ($angka < 100) { $terbilang = terbilang((int)($angka / 10)) . " puluh" . terbilang($angka % 10); }
                        elseif ($angka < 200) { $terbilang = " seratus" . terbilang($angka - 100); }
                        elseif ($angka < 1000) { $terbilang = terbilang((int)($angka / 100)) . " ratus" . terbilang($angka % 100); }
                        elseif ($angka < 2000) { $terbilang = " seribu" . terbilang($angka - 1000); }
                        elseif ($angka < 1000000) { $terbilang = terbilang((int)($angka / 1000)) . " ribu" . terbilang($angka % 1000); }
                        elseif ($angka < 1000000000) { $terbilang = terbilang((int)($angka / 1000000)) . " juta" . terbilang($angka % 1000000); }
                        return $terbilang;
                    } }
                    $nominal_total = $surat->total_nominal ?? 0;
                    $teks_terbilang = "#" . trim(terbilang($nominal_total)) . " rupiah #";
                @endphp
                Terbilang : &nbsp;&nbsp; {{ ucwords($teks_terbilang) }}
            </td>
        </tr>
    </table>

    <p style="font-size: 11px; font-style: italic; color: #666; margin-bottom: 20px;">Status Terakhir Dokumen di Sistem: <strong>{{ $surat->status_surat }}</strong> (Aliran Terakhir: {{ $surat->posisi_saat_ini }})</p>

    @if(!empty($ttd) && is_array($ttd))
    @php $cols = 3; @endphp
    <table class="ttd-table">
        @foreach(array_chunk($ttd, $cols) as $chunk)
        <tr>
            @foreach($chunk as $sig)
            @if($sig && is_array($sig))
            <td class="ttd-cell" style="width: {{ 100 / $cols }}%;">
                <div class="ttd-label">{{ $sig['label'] ?? '' }}</div>
                @if(!empty($sig['img_base64']))
                    <img src="{{ $sig['img_base64'] }}" class="signature-img"><br>
                @else
                    <br><br><br><br>
                @endif
                ( <strong>{{ $sig['nama_lengkap'] ?? '-' }}</strong> )
                <br><span class="ttd-role">{{ $sig['role_detail'] ?? '' }}</span>
            </td>
            @endif
            @endforeach
            @if(count($chunk) < $cols)
                @for($i = count($chunk); $i < $cols; $i++)
                <td class="ttd-cell" style="width: {{ 100 / $cols }}%;">&nbsp;</td>
                @endfor
            @endif
        </tr>
        @endforeach
    </table>
    @endif

</body>
</html>