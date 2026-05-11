<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Global - Laras Laundry</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#333; margin:15mm; line-height:1.4; }
        .header { text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:20px; }
        .header h1 { margin:0; font-size:26px; letter-spacing:2px; text-transform:uppercase; }
        .header .sub-title { margin:5px 0; font-size:16px; font-weight:bold; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; }
        th { background:#f2f2f2; text-transform:uppercase; }
        .total-row { font-weight:bold; background:#eee; }
        .badge { padding:3px 7px; border-radius:4px; font-weight:bold; font-size:10px; text-transform:uppercase; }
        .bg-pusat { background-color:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
        .bg-a { background-color:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .bg-b { background-color:#fff3cd; color:#856404; border:1px solid #ffeeba; }
        .box-laba { background:#f9f9f9; padding:15px; border:1px solid #ccc; width:300px; margin-left:auto; border-radius:5px; margin-bottom:20px; }
        .footer-container { width:100%; display:flex; flex-direction:column; align-items:flex-end; margin-top:20px; }
        .tanda-tangan { text-align:center; width:300px; font-size:13px; }
    </style>
</head>
<body id="area-laporan">
    <div class="header">
        <h1>Laras Laundry</h1>
        <div class="sub-title">LAPORAN KESELURUHAN (GLOBAL)</div>
        <hr style="border:0.5px solid #000; margin:10px 0 5px 0;">
        <small style="text-transform:uppercase; font-weight:bold; color:#555;">{{ $judulPeriode }}</small>
    </div>

    <h3>A. Rincian Pemasukan Gabungan</h3>
    <table>
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Cabang</th><th>Pelanggan</th><th>Layanan</th><th>Total Bayar</th></tr>
        </thead>
        <tbody>
            @foreach($transaksiList as $no => $t)
            @php
                $namaCabang = $t->outlet->nama_cabang ?? '-';
                $bgClass = str_contains($namaCabang, 'Pusat') ? 'bg-pusat' : (str_contains($namaCabang, 'A') ? 'bg-a' : 'bg-b');
            @endphp
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($t->tgl_masuk)->format('d/m/Y') }}</td>
                <td><span class="badge {{ $bgClass }}">{{ $namaCabang }}</span></td>
                <td>{{ ucwords(strtolower($t->pelanggan->nama_pelanggan ?? '-')) }}</td>
                <td>{{ $t->layanan->nama_layanan ?? '-' }}</td>
                <td>Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" align="right">TOTAL PEMASUKAN</td>
                <td>Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>B. Rincian Pengeluaran Gabungan</h3>
    <table>
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Cabang</th><th>Keterangan</th><th>Jumlah</th></tr>
        </thead>
        <tbody>
            @foreach($pengeluaranList as $no => $p)
            @php
                $namaCabangP = $p->outlet->nama_cabang ?? '-';
                $bgClassP = str_contains($namaCabangP, 'Pusat') ? 'bg-pusat' : (str_contains($namaCabangP, 'A') ? 'bg-a' : 'bg-b');
            @endphp
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tgl_pengeluaran)->format('d/m/Y') }}</td>
                <td><span class="badge {{ $bgClassP }}">{{ $namaCabangP }}</span></td>
                <td>{{ $p->keterangan }}</td>
                <td>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" align="right">TOTAL PENGELUARAN</td>
                <td>Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="box-laba">
        <table style="border:none; margin:0;">
            <tr style="border:none;"><td style="border:none; padding:2px;">Total Pemasukan</td><td style="border:none; text-align:right; padding:2px;">Rp {{ number_format($totalIn, 0, ',', '.') }}</td></tr>
            <tr style="border:none;"><td style="border:none; padding:2px;">Total Pengeluaran</td><td style="border:none; text-align:right; color:red; padding:2px;">- Rp {{ number_format($totalOut, 0, ',', '.') }}</td></tr>
            <tr style="border:none; border-top:2px solid #333; font-weight:bold;"><td style="border:none; padding-top:10px;">PROFIT BERSIH</td><td style="border:none; text-align:right; color:green; padding-top:10px; font-size:15px;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="footer-container">
        <div class="tanda-tangan">
            Banjarmasin, {{ \Carbon\Carbon::now()->format('d F Y') }}<br>
            Waktu Cetak: {{ \Carbon\Carbon::now()->format('H:i') }} WITA
            <br><br><br><br><br>
            <b>( Laraswati )</b>
        </div>
    </div>

    <script>
        window.onload = function() {
            const element = document.getElementById('area-laporan');
            const opt = {
                margin: [10, 10],
                filename: 'Laporan_Global_Laras_Laundry.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save().then(() => {
                setTimeout(() => { window.close(); }, 500);
            });
        };
    </script>
</body>
</html>
