<!DOCTYPE html>
<html>
<head>
    <title>Cetak Struk #{{ $transaksi->id_transaksi }}</title>
    <style>
        @page { size: 48mm auto; margin: 0; }
        body { font-family: 'Courier New', monospace; width: 48mm; margin: 0 auto; padding: 5mm 0; font-size: 11px; color: black; background: white; }
        .text-center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; }
        .bold { font-weight: bold; }
        .area-tombol { text-align: center; margin-bottom: 20px; }
        .btn-kembali {
            display: inline-block; padding: 10px 20px;
            background: #00d4aa; color: white; text-decoration: none;
            border-radius: 8px; font-family: 'Segoe UI', sans-serif;
            font-weight: bold; font-size: 14px; border: none; cursor: pointer;
        }
        @media print {
            .area-tombol { display: none !important; }
            body { margin: 0; padding: 0; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="area-tombol">
        <a href="{{ route('kasir') }}" class="btn-kembali">← Kembali ke Kasir</a>
        <p style="font-size: 10px; color: #666; margin-top: 5px;">(Tombol ini tidak akan ikut tercetak)</p>
    </div>

    <div class="text-center">
        <span>🧺</span><br>
        <strong style="font-size: 13px;">LARAS LAUNDRY</strong><br>
        <span style="font-size: 9px;">{{ $transaksi->outlet->alamat_outlet }}</span>
    </div>
    <div class="line"></div>
    <table>
        <tr><td>No. TRX</td><td align="right">#{{ str_pad($transaksi->id_transaksi, 4, "0", STR_PAD_LEFT) }}</td></tr>
        <tr><td>Tgl</td><td align="right">{{ \Carbon\Carbon::parse($transaksi->tgl_masuk)->format('d/m/y H:i') }}</td></tr>
        <tr><td>Cabang</td><td align="right">{{ $transaksi->outlet->nama_cabang }}</td></tr>
    </table>
    <div class="line"></div>
    <table class="bold">
        <tr>
            <td>{{ strtoupper($transaksi->pelanggan->nama_pelanggan) }}</td>
            <td align="right">{{ $transaksi->pelanggan->no_hp }}</td>
        </tr>
    </table>
    <div class="line"></div>
    <table>
        <tr><td colspan="2">{{ $transaksi->layanan->nama_layanan }}</td></tr>
        <tr>
            <td>{{ $transaksi->berat_kg }}kg x {{ number_format($transaksi->layanan->harga, 0, ',', '.') }}</td>
            <td align="right">Rp{{ number_format($total, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div class="line"></div>
    <table>
        <tr class="bold"><td>Total</td><td align="right">Rp{{ number_format($total, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar</td><td align="right">Rp{{ number_format($bayar, 0, ',', '.') }}</td></tr>
        <tr><td>Kembali</td><td align="right">Rp{{ number_format($kembali, 0, ',', '.') }}</td></tr>
    </table>

    @if(!empty($transaksi->catatan))
    <div class="line"></div>
    <div style="font-size: 10px;">
        <b>Catatan:</b><br>
        {{ $transaksi->catatan }}
    </div>
    @endif

    <div class="line"></div>
    <div class="text-center" style="font-size: 9px; margin-top: 10px;">
        HP. 0813 5154 3883<br>
        WA. 0821 4812 0213<br>
        --- TERIMA KASIH ---<br>
        Serahkan struk saat ambil cucian
    </div>

</body>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    if (urlParams.get('mode') === 'silent') {
        window.onload = function() {
            window.print();
            if (isMobile) {
                setTimeout(function() {
                    window.history.back();
                }, 1000);
            }
        };
    }
</script>
</html>
