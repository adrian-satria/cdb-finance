<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>LPJ Uang Muka - {{ $lpj->no_lpj }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #000; margin: 16px; }
        .title { text-align: center; font-weight: bold; font-size: 15px; letter-spacing: 1px; }
        .subtitle { text-align: center; margin-bottom: 14px; }
        table.form { width: 100%; border-collapse: collapse; }
        table.form td { padding: 3px 4px; vertical-align: top; }
        .label { width: 22%; white-space: nowrap; }
        .colon { width: 6px; }
        table.detil { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detil th, table.detil td { border: 1px solid #000; padding: 4px; }
        table.detil th { background: #f2f2f2; }
        .right { text-align: right; }
        .sum td { border: 1px solid #000; padding: 4px; }
        .terbilang { margin-top: 10px; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 30px; }
        table.sign td { text-align: center; vertical-align: bottom; height: 58px; width: 20%; padding: 0 4px; border-top: 1px solid #000; }
        .sign-sub { font-size: 11px; color: #333; }
    </style>
</head>
<body>
    <div class="title">LAPORAN PERTANGGUNGJAWABAN UANG MUKA [LPJUM]</div>
    <div class="subtitle">Finance Management Demo</div>

    <table class="form">
        <tr>
            <td class="label">No Referensi</td><td class="colon">:</td><td colspan="4"><strong>{{ $lpj->no_lpj }}</strong></td>
        </tr>
        <tr>
            <td class="label">Penanggungjawab</td><td class="colon">:</td><td width="32%">{{ $penanggungjawab }}</td>
            <td class="label">Project</td><td class="colon">:</td><td>{{ $projectName }}</td>
        </tr>
        <tr>
            <td class="label">Kegiatan</td><td class="colon">:</td><td colspan="4">{{ $kegiatan }}</td>
        </tr>
        <tr>
            <td class="label">Kode Aktivitas</td><td class="colon">:</td><td colspan="4">{{ $kodeAktivitas }}</td>
        </tr>
    </table>

    <table class="detil">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="14%">TANGGAL</th>
                <th width="46%">URAIAN</th>
                <th width="20%" class="right">JUMLAH</th>
                <th width="15%">NO BUKTI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lpj->details as $d)
                <tr>
                    <td class="right">{{ $loop->iteration }}</td>
                    <td>{{ ($d->tanggal ?? $lpj->tanggal)?->format('d/m/Y') }}</td>
                    <td>{{ $d->keterangan }}</td>
                    <td class="right">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                    <td>{{ $d->no_bukti ?? '' }}</td>
                </tr>
            @endforeach
            @for($i = $lpj->details->count(); $i < 8; $i++)
                <tr><td class="right">{{ $i + 1 }}</td><td></td><td></td><td></td><td></td></tr>
            @endfor
            <tr class="sum">
                <td colspan="3" class="right"><strong>Jumlah Pengeluaran</strong></td>
                <td class="right"><strong>Rp {{ number_format($a, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="form" style="margin-top:8px;">
        <tr>
            <td class="label">Nomor UM</td><td class="colon">:</td><td width="20%">{{ $um->no_aju }}</td>
            <td class="label">tanggal UM</td><td class="colon">:</td><td width="14%">{{ $um->tanggal?->format('d/m/Y') }}</td>
            <td class="right"><strong>Rp {{ number_format($b, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">Saldo</td><td class="colon">:</td>
            <td class="right" colspan="5"><strong>Rp {{ number_format($saldo, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="terbilang"><strong>Terbilang :</strong> {{ $terbilang }}</div>

    <table class="sign">
        <tr>
            <td>Dibuat oleh<br><br><strong>{{ $penanggungjawab }}</strong></td>
            <td>Dikoreksi dan dicatat<br><br>(Kasir)</td>
            <td>Diketahui<br><br>(AM)</td>
            <td>Diverifikasi<br><br>(FP/Akunting)</td>
            <td>Diketahui<br><br>(PM)</td>
        </tr>
        <tr>
            <td class="sign-sub">Pelaksana<br>Tgl :</td>
            <td class="sign-sub">Kasir<br>Tgl :</td>
            <td class="sign-sub">AM<br>Tgl :</td>
            <td class="sign-sub">FP/Akunting<br>Tgl :</td>
            <td class="sign-sub">PM<br>Tgl :</td>
        </tr>
    </table>
</body>
</html>

