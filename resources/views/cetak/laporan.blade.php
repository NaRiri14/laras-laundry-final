<!DOCTYPE html>
<html>
<head>
    <title>Laporan {{ $outlet->nama_cabang }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family:'Segoe UI', Tahoma, sans-serif; color:#333; margin:0; background:#fff; padding:20px; }
        .header { text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:20px; }
        .header h1 { margin:0; font-size:24px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th, td { border:1px solid #999; padding:8px; text-align:left; font-size:12px; }
        th { background:#f2f2f2; text-transform:uppercase; }
        .total-row { font-weight:bold; background:#eee; }
        .box-laba { background:#f9f9f9; padding:15px; border:1px solid #ccc; width:300px; margin-left:auto; border-radius:5px; }
        .tanda-tangan { text-align:center; width:250px; float:right; margin-top:30px; }
    </style>
</head>
<body id="halaman-laporan">
    <div class="header">
        <h1>Laras Laundry</h1>
        <p><b>{{ $outlet->nama_cabang }}</b></p>
        <p>{{ $outlet->alamat_outlet }}</p>
        <hr>
        <small><b>{{ strtoupper($judulPeriode) }}</b></small>
    </div>

    <h3>A. Rincian Pemasukan</h3>
    <table>
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Layanan</th><th>Total Bayar</th></tr>
        </thead>
        <tbody>
            @foreach($transaksiList as $no => $t)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($t->tgl_masuk)->format('d/m/Y') }}</td>
                <td>{{ $t->pelanggan->nama_pelanggan ?? '-' }}</td>
                <td>{{ $t->layanan->nama_layanan ?? '-' }}</td>
                <td>Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" align="right">TOTAL PEMASUKAN</td>
                <td>Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h3>B. Rincian Pengeluaran</h3>
    <table>
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Keterangan</th><th>Jumlah</th></tr>
        </thead>
        <tbody>
            @foreach($pengeluaranList as $no => $p)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tgl_pengeluaran)->format('d/m/Y') }}</td>
                <td>{{ $p->keterangan }}</td>
                <td>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" align="right">TOTAL PENGELUARAN</td>
                <td>Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="box-laba">
        <table style="border:none; margin:0; width:100%;">
            <tr style="border:none;"><td style="border:none;">Total Pemasukan</td><td style="border:none; text-align:right;">Rp {{ number_format($totalIn, 0, ',', '.') }}</td></tr>
            <tr style="border:none;"><td style="border:none;">Total Pengeluaran</td><td style="border:none; text-align:right; color:red;">- Rp {{ number_format($totalOut, 0, ',', '.') }}</td></tr>
            <tr style="border:none; border-top:2px solid #333; font-weight:bold;"><td style="border:none;">LABA BERSIH</td><td style="border:none; text-align:right; color:green;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="tanda-tangan">
        Banjarmasin, {{ \Carbon\Carbon::now()->format('d F Y') }}<br><br><br><br>
        <b>( Laraswati )</b>
    </div>

    <script>
        window.onload = function() {
            const element = document.getElementById('halaman-laporan');
            const opsi = {
                margin: [10, 10, 10, 10],
                filename: 'Laporan_Laundry_{{ Carbon\Carbon::now()->format("dmY") }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, logging: false },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opsi).from(element).save().then(() => {
                window.close();
            });
        };
    </script>
</body>
</html>
