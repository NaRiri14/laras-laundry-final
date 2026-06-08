@extends('layouts.kasir')
@section('title', 'Kasir')

@push('styles')
<style>
    * { box-sizing: border-box; }
    .main-wrapper { margin-left:260px; padding:15px 25px; color:white; min-height:100vh; }
    .flex-container { display:flex; gap:20px; flex-wrap:wrap; width:100%; }
    .col-kiri { flex:1.5; width:100%; }
    .col-kanan { flex:0.8; width:100%; }
    .card-dark { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:15px; margin-bottom:15px; }
    .form-box { width:100%; background:#0d1117; border:1px solid #30363d; color:white; padding:10px 12px; border-radius:8px; margin-bottom:10px; font-size:13px; outline:none; }
    .form-box option { background:#0d1117; }
    label { display:block; color:#8b949e; font-size:11px; margin-bottom:5px; font-weight:bold; }
    .box-total { background:#0d1614; padding:15px 20px; border-radius:10px; border:1px solid #00d4aa33; margin:15px 0; display:flex; justify-content:space-between; align-items:center; }
    @media screen and (max-width:768px){
        .main-wrapper { margin-left:0!important; padding:10px!important; width:100%!important; }
        .col-kiri, .col-kanan { flex:1 1 100%!important; max-width:100%!important; }
        .row-flex { flex-direction:column!important; gap:0!important; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">

    {{-- Toast setelah simpan --}}
    @if(session('success'))
    <div id="toast" style="position:fixed; top:20px; right:20px; background:#00d4aa; color:#0d1117; padding:12px 20px; border-radius:10px; font-weight:bold; font-size:13px; z-index:9999; box-shadow:0 4px 15px rgba(0,212,170,0.3);">
        ✅ {{ session('success') }}
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('toast').style.opacity = '0';
            document.getElementById('toast').style.transition = 'opacity 0.5s';
            setTimeout(() => document.getElementById('toast').remove(), 500);
        }, 2500);
    </script>
    @endif

    {{-- Auto print setelah simpan --}}
    @if(session('print_id'))
    <script>
        window.addEventListener('load', function() {
            silentPrint('{{ session('print_id') }}', '{{ session('print_bayar') }}');
        });
    </script>
    @endif

    <div class="flex-container">

        {{-- KOLOM KIRI: Form Transaksi --}}
        <div class="col-kiri">
            <div class="card-dark">
                <h4 style="color:#00d4aa;margin-top:0;">🧺 Transaksi Baru</h4>
                <form action="{{ route('kasir.simpan') }}" method="POST">
                    @csrf

                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" id="nama_pelanggan"
                        placeholder="Nama..." class="form-box" required>

                    <div class="row-flex" style="display:flex;gap:15px;">
                        <div style="flex:1;">
                            <label>No. HP</label>
                            <input type="text" name="no_hp" id="no_hp"
                                placeholder="08..." class="form-box" onkeyup="cekPelanggan()">
                        </div>
                        <div style="flex:1;">
                            <label>Layanan</label>
                            <select name="id_layanan" id="layanan" onchange="hitung()" class="form-box" required>
                                <option value="" data-harga="0">Pilih layanan...</option>
                                @foreach($layanan as $l)
                                <option value="{{ $l->id_layanan }}" data-harga="{{ $l->harga }}">
                                    {{ $l->nama_layanan }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="width:50%;">
                        <label>Berat (kg)</label>
                        <input type="number" name="berat_kg" id="berat"
                            value="0" min="0.1" step="0.1" required oninput="hitung()" class="form-box">
                    </div>

                    <div class="box-total">
                        <span style="color:#8b949e;font-size:13px;">Total Bayar</span>
                        <h2 id="txtTotal" style="color:#00d4aa;margin:0;font-family:'Syne';font-size:26px;">Rp 0</h2>
                        <input type="hidden" name="total_bayar" id="inputTotal" value="0">
                    </div>

                    <div class="row-flex" style="display:flex;gap:15px;">
                        <div style="flex:1;">
                            <label>Bayar (Rp)</label>
                            {{-- PERUBAHAN DI SINI --}}
                            <input type="number" name="bayar" id="bayar"
                                placeholder="Masukkan jumlah bayar..." min="1" required oninput="hitung()" class="form-box">
                        </div>
                        <div style="flex:1;">
                            <label>Kembalian</label>
                            <div id="txtKembali" style="color:#00d4aa;font-weight:bold;font-size:18px;margin-top:5px;">Rp 0</div>
                        </div>
                    </div>

                    <label>Catatan</label>
                    <input type="text" name="catatan"
                        placeholder="Tambahkan catatan jika ada..." class="form-box">

                    <button type="submit" style="width:100%;background:#00d4aa;color:#0d1117;padding:12px;border-radius:8px;border:none;font-weight:bold;cursor:pointer;margin-top:10px;font-size:14px;">
                        💾 Simpan & Cetak
                    </button>
                </form>
            </div>
        </div>

        {{-- KOLOM KANAN: Ringkasan & Antrian --}}
        <div class="col-kanan">
            <div class="card-dark">
                <h4 style="color:#00d4aa;margin-top:0;">💰 Ringkasan Kas</h4>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div style="background:#0d1117;padding:10px 15px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#8b949e;font-size:12px;">Pemasukan</span>
                        <b style="color:#00d4aa;font-size:13px;">Rp {{ number_format($pemasukan,0,',','.') }}</b>
                    </div>
                    <div style="background:rgba(239,68,68,0.05);padding:10px 15px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#8b949e;font-size:12px;">Pengeluaran</span>
                        <b style="color:#ef4444;font-size:13px;">Rp {{ number_format($pengeluaran,0,',','.') }}</b>
                    </div>
                    <div style="background:rgba(59,130,246,0.08);padding:10px 15px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;border:1px solid rgba(59,130,246,0.2);">
                        <span style="color:#8b949e;font-size:12px;">Laba bersih</span>
                        <b style="color:#3b82f6;font-size:13px;">Rp {{ number_format($kasBersih,0,',','.') }}</b>
                    </div>
                </div>
            </div>

            <div class="card-dark">
                <h4 style="color:#00d4aa;margin-top:0;">🕒 Antrian</h4>
                @forelse($antrian as $a)
                <div style="padding:10px 0;border-bottom:1px solid #30363d;font-size:12px;">
                    <b style="color:#00d4aa;">#{{ str_pad($a->id_transaksi,3,'0',STR_PAD_LEFT) }}</b>
                    - {{ $a->pelanggan->nama_pelanggan ?? '-' }}
                </div>
                @empty
                <p style="color:#8b949e;font-size:12px;">Tidak ada antrian.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function silentPrint(id, bayar) {
    window.open(
        '{{ route("cetak.struk") }}?id=' + id + '&bayar=' + bayar,
        '_blank'
    );
}

function hitung() {
    const layanan = document.getElementById('layanan');
    const harga = parseFloat(layanan.options[layanan.selectedIndex].getAttribute('data-harga')) || 0;
    const berat = parseFloat(document.getElementById('berat').value) || 0;
    const bayar = parseFloat(document.getElementById('bayar').value) || 0;
    const total = Math.round(harga * berat);
    const kembali = Math.round(bayar - total);
    document.getElementById('txtTotal').innerText = "Rp " + total.toLocaleString('id-ID');
    document.getElementById('inputTotal').value = total;
    document.getElementById('txtKembali').innerText = "Rp " + (kembali < 0 ? 0 : kembali).toLocaleString('id-ID');
}

function cekPelanggan() {
    let hp = document.getElementById('no_hp').value;
    if (hp.length >= 5) {
        fetch('{{ route("kasir.cek_hp") }}?hp=' + hp)
            .then(r => r.json())
            .then(data => {
                if (data && data.nama_pelanggan) {
                    document.getElementById('nama_pelanggan').value = data.nama_pelanggan;
                    document.getElementById('nama_pelanggan').style.border = "1px solid #00d4aa";
                } else {
                    document.getElementById('nama_pelanggan').style.border = "1px solid #30363d";
                }
            });
    }
}
</script>
@endpush
