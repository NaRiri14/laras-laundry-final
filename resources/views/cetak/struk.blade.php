<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk #{{ $transaksi->id_transaksi }}</title>
    <style>
        @page { size: 58mm auto; margin: 0; }

        body {
            font-family: 'Courier New', monospace;
            width: 58mm;
            max-width: 58mm;
            margin: 0 auto;
            padding: 3mm;
            font-size: 13px;
            color: black;
            background: white;
            box-sizing: border-box;
        }

        .text-center { text-align: center; }
        .line { border-bottom: 1px dashed #000; margin: 4px 0; }
        .bold { font-weight: bold; }

        .area-tombol {
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }

        .btn-kembali {
            display: inline-block;
            padding: 10px 20px;
            background: #00d4aa;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-family: 'Segoe UI', sans-serif;
            font-weight: bold;
            font-size: 14px;
        }

        @media screen {
            body {
                width: 100% !important;
                max-width: 400px !important;
                font-size: 14px !important;
            }
            .area-tombol { display: block; }
        }

        @media print {
            @page { size: 58mm auto; margin: 0; }
            body {
                width: 58mm !important;
                max-width: 58mm !important;
                padding: 3mm !important;
                font-size: 13px !important;
                margin: 0 !important;
            }
            .area-tombol { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="area-tombol">
        <a href="{{ route('kasir') }}" class="btn-kembali">← Kembali</a>
        &nbsp;
        <button onclick="window.print()" class="btn-kembali">🖨️ Cetak</button>
    </div>

    <div id="struk">

        <div class="text-center">
            🧺<br>
            <strong>LARAS LAUNDRY</strong><br>
            <small>{{ $transaksi->outlet->alamat_outlet }}</small>
        </div>

        <div class="line"></div>
        <div>No. TRX : #{{ str_pad($transaksi->id_transaksi, 4, "0", STR_PAD_LEFT) }}</div>
        <div>Tgl     : {{ \Carbon\Carbon::parse($transaksi->tgl_masuk)->format('d/m/y H:i') }}</div>
        <div>Cabang  : {{ $transaksi->outlet->nama_cabang }}</div>

        <div class="line"></div>
        <div class="bold">{{ strtoupper($transaksi->pelanggan->nama_pelanggan) }}</div>
        <div>{{ $transaksi->pelanggan->no_hp }}</div>

        <div class="line"></div>
        <div>{{ $transaksi->layanan->nama_layanan }}</div>
        <div>{{ $transaksi->berat_kg }}kg x Rp{{ number_format($transaksi->layanan->harga, 0, ',', '.') }}</div>
        <div class="bold">= Rp{{ number_format($total, 0, ',', '.') }}</div>

        <div class="line"></div>
        <div>Total   : Rp{{ number_format($total, 0, ',', '.') }}</div>
        <div>Bayar   : Rp{{ number_format($bayar, 0, ',', '.') }}</div>
        <div>Kembali : Rp{{ number_format($kembali, 0, ',', '.') }}</div>

        @if(!empty($transaksi->catatan))
        <div class="line"></div>
        <div>Catatan: {{ $transaksi->catatan }}</div>
        @endif

        <div class="line"></div>
        <div class="text-center">
            HP. 0813 5154 3883<br>
            WA. 0821 4812 0213<br>
            --- TERIMA KASIH ---<br>
            Serahkan struk saat ambil cucian
        </div>

    </div>

</body>
<script>
    if (new URLSearchParams(window.location.search).get('mode') === 'silent') {
        window.onload = function() { window.print(); };
    }
</script>
</html>