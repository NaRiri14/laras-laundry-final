<!DOCTYPE html>
<html>
<head>
    <title>Laporan Global Laras Laundry</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family:'Segoe UI', Tahoma, sans-serif; color:#333; margin:0; background:#fff; padding:20px; }
        .header { text-align:center; border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:20px; }
        .header h1 { margin:0; font-size:24px; }
        .box-ringkasan { background:#f9f9f9; padding:20px; border:1px solid #ccc; border-radius:5px; max-width:400px; margin:30px auto; }
        .box-ringkasan table { width:100%; border-collapse:collapse; }
        .box-ringkasan td { padding:8px 5px; font-size:14px; border:none; }
        .box-ringkasan .laba { font-size:18px; font-weight:bold; color:green; border-top:2px solid #333; padding-top:10px; }
        .box-detail { max-width:400px; margin:20px auto; border:1px solid #ccc; border-radius:5px; padding:15px 20px; }
        .box-detail h3 { text-align:center; margin-top:0; font-size:15px; }
        .box-detail table { width:100%; border-collapse:collapse; font-size:13px; }
        .box-detail td { padding:5px 3px; border-bottom:1px solid #eee; }
        .box-detail .total-row td { border-top:2px solid #333; border-bottom:none; font-weight:bold; padding-top:8px; }
        .empty-note { text-align:center; color:#888; font-size:13px; font-style:italic; }
        .tanda-tangan { text-align:center; width:250px; margin:40px auto 0 auto; }
    </style>
</head>
<body id="halaman-laporan">
    <div class="header">
        <h1>Laras Laundry</h1>
        <p><b>Laporan Global Semua Cabang</b></p>
        <hr>
        <small><b>{{ strtoupper($judulPeriode) }}</b></small>
    </div>

    <div class="box-ringkasan">
        <h3 style="text-align:center; margin-top:0;">Ringkasan Laporan</h3>
        <table>
            <tr>
                <td>Total Transaksi</td>
                <td align="right"><b>{{ $transaksiList->count() }} order</b></td>
            </tr>
            <tr>
                <td>Total Berat</td>
                <td align="right"><b>{{ number_format($transaksiList->sum('berat_kg'), 1, ',', '.') }} kg</b></td>
            </tr>
            <tr>
                <td>Total Pemasukan</td>
                <td align="right"><b>Rp {{ number_format($totalIn, 0, ',', '.') }}</b></td>
            </tr>
            <tr>
                <td>Total Pengeluaran</td>
                <td align="right"><b>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</b></td>
            </tr>
            <tr class="laba">
                <td>Total Laba Bersih</td>
                <td align="right">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="box-detail">
        <h3>Riwayat Singkat Layanan</h3>
        @if($riwayatLayanan->count())
            <table>
                @foreach($riwayatLayanan as $namaLayanan => $jumlah)
                    <tr>
                        <td>{{ $namaLayanan }}</td>
                        <td align="right">{{ $jumlah }}x</td>
                    </tr>
                @endforeach
            </table>
        @else
            <p class="empty-note">Tidak ada transaksi pada periode ini.</p>
        @endif
    </div>

    <div class="box-detail">
        <h3>Rincian Pengeluaran</h3>
        @if($pengeluaranList->count())
            <table>
                @foreach($pengeluaranList as $pengeluaran)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($pengeluaran->tgl_pengeluaran)->format('d/m/Y') }}
                            - {{ $pengeluaran->keterangan }}
                            <br><small style="color:#888;">{{ $pengeluaran->outlet->nama_cabang ?? '-' }}</small>
                        </td>
                        <td align="right">Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total Pengeluaran</td>
                    <td align="right">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                </tr>
            </table>
        @else
            <p class="empty-note">Tidak ada pengeluaran pada periode ini.</p>
        @endif
    </div>

    @foreach($riwayatJatahPerCabang as $namaCabang => $riwayat)
    <div class="box-detail">
        <h3>Jatah Operasional per Minggu — {{ $namaCabang }}</h3>
        <table>
            <tr style="background:#f0f0f0; font-weight:bold; font-size:12px;">
                <td>Periode Minggu</td>
                <td align="right">Jatah</td>
                <td align="right">Terpakai</td>
                <td align="right">Sisa / Lebih</td>
            </tr>
            @foreach($riwayat as $r)
            <tr>
                <td style="font-size:12px;">{{ $r['label'] }}</td>
                <td align="right" style="font-size:12px;">Rp {{ number_format($r['jatah'], 0, ',', '.') }}</td>
                <td align="right" style="font-size:12px;">Rp {{ number_format($r['pengeluaran'], 0, ',', '.') }}</td>
                <td align="right" style="font-size:12px; color:{{ $r['sisa'] >= 0 ? 'green' : 'red' }}; font-weight:bold;">
                    {{ $r['sisa'] >= 0 ? 'Sisa' : 'Lebih' }} Rp {{ number_format(abs($r['sisa']), 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endforeach
    <p style="font-size:11px; color:#888; text-align:center; max-width:400px; margin:0 auto 20px;">* Jatah operasional Rp 500.000 per minggu per cabang. Laba bersih hanya berkurang jika pengeluaran melebihi jatah.</p>

    <div class="tanda-tangan">
        Banjarmasin, {{ \Carbon\Carbon::now()->format('d F Y') }}<br><br><br><br>
        <b>( Laraswati )</b>
    </div>

    <script>
        window.onload = function() {
            const element = document.getElementById('halaman-laporan');
            const opsi = {
                margin: [15, 15, 15, 15],
                filename: 'Laporan_Global_{{ Carbon\Carbon::now()->format("dmY") }}.pdf',
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
