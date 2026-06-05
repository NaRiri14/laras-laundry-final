@extends('layouts.kasir')
@section('title', 'Pengeluaran Operasional')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:25px; color:white; min-height:100vh; background:#0d1117; }
    .card-dark { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px; margin-bottom:20px; }
    .form-box { width:100%; background:#0d1117; border:1px solid #30363d; color:white; padding:10px 12px; border-radius:8px; margin-bottom:12px; font-size:13px; outline:none; box-sizing:border-box; }
    label { display:block; color:#8b949e; font-size:11px; margin-bottom:5px; font-weight:bold; }
    .btn-simpan { width:100%; background:#00d4aa; color:#0d1117; padding:12px; border-radius:8px; border:none; font-weight:bold; cursor:pointer; font-size:14px; }
    table { width:100%; border-collapse:collapse; }
    th { text-align:left; color:#8b949e; font-size:11px; padding:12px 10px; border-bottom:2px solid #30363d; text-transform:uppercase; }
    td { padding:12px 10px; border-bottom:1px solid #30363d; font-size:13px; }
    .modal-foto { display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.9); align-items:center; justify-content:center; }
    .modal-foto img { max-width:90%; max-height:85vh; border-radius:12px; display:block; margin:auto; }
    .close-foto { position:absolute; top:20px; right:30px; color:white; font-size:36px; cursor:pointer; }

    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:15px !important; }
        .grid-layout { grid-template-columns:1fr !important; }
        table thead { display:none; }
        table tr { display:block; background:#0d1117; border:1px solid #30363d; border-radius:10px; padding:10px; margin-bottom:10px; }
        table td { display:flex; justify-content:space-between; align-items:center; padding:8px 5px !important; border:none; border-bottom:1px solid #30363d55 !important; font-size:12px; }
        table td:last-child { border-bottom:none !important; }
        table td::before { content:attr(data-label); font-weight:bold; color:#8b949e; font-size:10px; text-transform:uppercase; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">

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

    <div style="margin-bottom:25px;">
        <h2 style="font-family:'Syne'; margin:0; color:#00d4aa;">💸 Pengeluaran Operasional</h2>
        <p style="color:#8b949e; font-size:13px; margin-top:5px;">Catat semua pengeluaran harian cabang.</p>
    </div>

    <div class="grid-layout" style="display:grid; grid-template-columns:1fr 2fr; gap:25px;">

        {{-- FORM --}}
        <div class="card-dark">
            <h4 style="margin-top:0; color:#00d4aa;">
                {{ isset($edit) ? '✏️ Edit Pengeluaran' : '➕ Tambah Pengeluaran' }}
            </h4>
            <form method="POST" action="{{ isset($edit) ? route('pengeluaran.update', $edit->id_pengeluaran) : route('pengeluaran.simpan') }}" enctype="multipart/form-data">
                @csrf

                <label>Keterangan</label>
                <input type="text" name="keterangan" class="form-box"
                    placeholder="Misal: Beli sabun..." required
                    value="{{ $edit->keterangan ?? '' }}">

                <label>Jumlah (Rp)</label>
                <input type="number" name="jumlah" class="form-box"
                    placeholder="50000" required
                    value="{{ $edit->jumlah ?? '' }}">

                <label>Foto Bukti (opsional)</label>
                @if(isset($edit) && $edit->foto_bukti)
                <div style="margin-bottom:10px;">
                    <small style="color:#8b949e;">Foto saat ini:</small><br>
                    <img src="{{ asset('img_bukti/'.$edit->foto_bukti) }}" style="max-width:100%; max-height:150px; border-radius:8px; margin-top:5px;">
                </div>
                @endif
                <input type="file" name="foto_bukti" class="form-box" accept="image/*">

                @if(isset($edit))
                <a href="{{ route('pengeluaran') }}" style="display:block; width:100%; background:#ff4d4d; color:white; padding:12px; border-radius:8px; font-weight:bold; font-size:14px; text-align:center; text-decoration:none; margin-bottom:10px; box-sizing:border-box;">❌ Batal Edit</a>
                @endif

                <button type="submit" class="btn-simpan">💾 Simpan</button>
            </form>
        </div>

        {{-- TABEL --}}
        <div class="card-dark">
            <h4 style="margin-top:0; color:#00d4aa;">📋 Riwayat Pengeluaran</h4>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                        <th>Bukti</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengeluaran as $row)
                    <tr>
                        <td data-label="Tanggal" style="color:#8b949e;">
                            {{ \Carbon\Carbon::parse($row->tgl_pengeluaran)->format('d/m/Y') }}
                        </td>
                        <td data-label="Keterangan">{{ $row->keterangan }}</td>
                        <td data-label="Jumlah" style="color:#ff4d4d; font-weight:bold;">
                            Rp {{ number_format($row->jumlah,0,',','.') }}
                        </td>
                        <td data-label="Bukti">
                            @if($row->foto_bukti)
                            <span onclick="lihatFoto('{{ asset('img_bukti/'.$row->foto_bukti) }}')"
                                style="cursor:pointer; font-size:18px;" title="Lihat Bukti">📸</span>
                            @else
                            <small style="color:#444;">-</small>
                            @endif
                        </td>
                        <td data-label="Aksi" style="text-align:center;">
                            <a href="{{ route('pengeluaran.edit', $row->id_pengeluaran) }}"
                                style="text-decoration:none; font-size:16px; margin-right:8px;" title="Edit">✏️</a>
                            <a href="{{ route('pengeluaran.hapus', $row->id_pengeluaran) }}"
                                onclick="return confirm('Hapus pengeluaran ini?')"
                                style="text-decoration:none; font-size:16px;" title="Hapus">🗑️</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#8b949e; padding:20px;">
                            Belum ada data pengeluaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL FOTO --}}
<div id="modalFoto" class="modal-foto" onclick="tutupFoto()">
    <span class="close-foto" onclick="tutupFoto()">&times;</span>
    <img id="imgFoto" src="">
</div>
@endsection

@push('scripts')
<script>
function lihatFoto(src) {
    document.getElementById('imgFoto').src = src;
    document.getElementById('modalFoto').style.display = 'flex';
}
function tutupFoto() {
    document.getElementById('modalFoto').style.display = 'none';
}
</script>
@endpush