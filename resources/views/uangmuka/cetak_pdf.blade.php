<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Uang Muka - {{ $um->no_aju }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #000; margin: 16px; }
        .title { text-align: center; font-weight: bold; font-size: 16px; letter-spacing: 1px; }
        .subtitle { text-align: center; margin-bottom: 18px; }
        table.form { width: 100%; border-collapse: collapse; }
        table.form td { padding: 3px 4px; vertical-align: top; }
        .label { width: 16%; white-space: nowrap; }
        .colon { width: 8px; }
        .box { border: 1px solid #000; padding: 8px; margin-top: 10px; min-height: 40px; }
        table.detil { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detil th, table.detil td { border: 1px solid #000; padding: 5px; }
        table.detil th { background: #f2f2f2; }
        .right { text-align: right; }
        .keperluan { margin-top: 10px; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 28px; }
        table.sign td { text-align: center; vertical-align: bottom; height: 64px; width: 16.6%; padding: 0 4px; }
        .sign-sub { font-size: 11px; color: #333; }
        .terbilang { margin-top: 12px; }
    </style>
</head>
<body>
    <div class="title">FORM PENGAJUAN UANG MUKA</div>
    <div class="subtitle">Finance Management Demo</div>

    <table class="form">
        <tr>
            <td class="label">NAMA</td><td class="colon">:</td><td width="38%">{{ $nama }}</td>
            <td class="label">Tanggal</td><td class="colon">:</td><td>{{ $um->tanggal?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">PROJECT</td><td class="colon">:</td><td>{{ $projectName }}</td>
            <td class="label">No UM</td><td class="colon">:</td><td>{{ $um->no_aju }}</td>
        </tr>
        <tr>
            <td class="label">KODE PROJECT</td><td class="colon">:</td><td colspan="4">{{ $um->kode_project }}</td>
        </tr>
    </table>

    <div class="keperluan">
        Dengan ini mengajukan Uang Muka untuk keperluan :
        <div class="box">{{ $um->keterangan ?? '-' }}</div>
    </div>

    <table class="detil">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="50%">Uraian</th>
                <th width="20%">Kode Aktivitas</th>
                <th width="25%" class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($um->details as $d)
                <tr>
                    <td class="right">{{ $loop->iteration }}</td>
                    <td>{{ $d->keterangan }}</td>
                    <td>{{ $d->kode_budget }}</td>
                    <td class="right">Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @for($i = $um->details->count(); $i < 5; $i++)
                <tr><td class="right">{{ $i + 1 }}</td><td>&nbsp;</td><td></td><td></td></tr>
            @endfor
            <tr>
                <td colspan="3" class="right"><strong>Total</strong></td>
                <td class="right"><strong>Rp {{ number_format($um->total_nominal, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="terbilang"><strong>Terbilang :</strong> {{ $terbilang }}</div>

    <table class="sign">
        <tr>
            <td>Akan diselesaikan tanggal<br>{{ $um->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
            <td>Pemohon,<br><br><strong>{{ $ttd['pemohon'] ?? '' }}</strong></td>
            <td>Diketahui</td>
            <td>Disetujui</td>
            <td>Dibayarkan tgl</td>
            <td>Dicatat kasir</td>
        </tr>
        <tr>
            <td></td>
            <td class="sign-sub">AM/ Kabid</td>
            <td class="sign-sub"></td>
            <td class="sign-sub">FP/Keuangan</td>
            <td class="sign-sub"></td>
            <td class="sign-sub"></td>
        </tr>
    </table>
</body>
</html>

