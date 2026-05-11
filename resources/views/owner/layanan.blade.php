@extends('layouts.owner')
@section('title', 'Kelola Layanan')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:25px; color:white; background:#0d1117; min-height:100vh; }
    .card-dark { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px; }
    .form-control { width:100%; padding:12px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:white; margin-bottom:15px; box-sizing:border-box; }
    .btn-save { background:#ff9f43; color:#0d1117; border:none; padding:12px 20px; border-radius:8px; font-weight:bold; cursor:pointer; width:100%; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th { text-align:left; padding:15px; border-bottom:2px solid #30363d; color:#8b949e; font-size:11px; text-transform:uppercase; }
    td { padding:15px; border-bottom:1px solid #30363d; font-size:14px; }
    .btn-edit { color:#3b82f6; text-decoration:none; border:1px solid #3b82f6; padding:5px 12px; border-radius:6px; font-size:12px; }
    .btn-delete { color:#ff4d4d; text-decoration:none; border:1px solid #ff4d4d; padding:5px 12px; border-radius:6px; font-size:12px; }
    .modal { display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.8); }
    .modal-box { background:#161b22; margin:10% auto; padding:25px; border:1px solid #30363d; width:400px; border-radius:12px; }

    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:15px; }
        .grid-layanan { grid-template-columns:1fr !important; }
        .modal-box { width:90%; margin:20% auto; }

        table thead { display:none; }
        table tr { display:block; background:#0d1117; border:1px solid #30363d; border-radius:10px; padding:10px; margin-bottom:10px; }
        table td { display:flex; justify-content:space-between; align-items:center; padding:8px 5px !important; border:none; border-bottom:1px solid #30363d55 !important; font-size:13px; }
        table td:last-child { border-bottom:none !important; }
        table td::before { content:attr(data-label); font-weight:bold; color:#8b949e; font-size:10px; text-transform:uppercase; }
        .btn-edit, .btn-delete { padding:4px 10px; font-size:11px; }
    }
</style>
@endpush

@section('content')

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

<div class="main-wrapper">
    <div style="margin-bottom:25px;">
        <h2 style="font-family:'Syne'; margin:0; color:#fff;">🛠️ Kelola Layanan & Harga</h2>
        <p style="color:#8b949e; font-size:14px;">Halaman khusus Owner untuk mengatur tarif laundry.</p>
    </div>

    <div class="grid-layanan" style="display:grid; grid-template-columns:1fr 2fr; gap:25px;">

        {{-- FORM TAMBAH --}}
        <div class="card-dark">
            <h4 style="margin-top:0;">➕ Tambah Layanan</h4>
            <form method="POST" action="{{ route('layanan.simpan') }}">
                @csrf
                <label style="font-size:11px; color:#8b949e;">NAMA LAYANAN</label>
                <input type="text" name="nama_layanan" class="form-control" placeholder="Misal: Cuci Kering" required>

                <label style="font-size:11px; color:#8b949e;">HARGA (RP)</label>
                <input type="number" name="harga" class="form-control" placeholder="7000" required>

                <button type="submit" class="btn-save">SIMPAN LAYANAN</button>
            </form>
        </div>

        {{-- TABEL LAYANAN --}}
        <div class="card-dark">
            <h4 style="margin-top:0;">📋 Daftar Harga Saat Ini</h4>
            <table>
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Layanan</th>
                        <th>Harga</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($layanan as $no => $row)
                    <tr>
                        <td data-label="No">{{ $no + 1 }}</td>
                        <td data-label="Layanan"><b>{{ $row->nama_layanan }}</b></td>
                        <td data-label="Harga" style="color:#00d4aa;">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                        <td data-label="Aksi">
                            <div style="display:flex; gap:6px;">
                                <a href="javascript:void(0)"
                                    onclick="openEdit('{{ $row->id_layanan }}','{{ $row->nama_layanan }}','{{ $row->harga }}')"
                                    class="btn-edit">Edit</a>
                                <a href="{{ route('layanan.hapus', $row->id_layanan) }}"
                                    onclick="return confirm('Hapus layanan ini?')"
                                    class="btn-delete">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEdit" class="modal">
    <div class="modal-box">
        <h4 style="margin-top:0;">✏️ Edit Data Layanan</h4>
        <form method="POST" action="{{ route('layanan.edit') }}">
            @csrf
            <input type="hidden" name="id_layanan" id="edit_id">

            <label style="font-size:11px; color:#8b949e;">NAMA LAYANAN</label>
            <input type="text" name="nama_layanan" id="edit_nama" class="form-control" required>

            <label style="font-size:11px; color:#8b949e;">HARGA (RP)</label>
            <input type="number" name="harga" id="edit_harga" class="form-control" required>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-save">UPDATE</button>
                <button type="button" onclick="closeEdit()"
                    style="background:#30363d; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; flex:1;">
                    BATAL
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, nama, harga) {
    document.getElementById('modalEdit').style.display = 'block';
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_harga').value = harga;
}
function closeEdit() {
    document.getElementById('modalEdit').style.display = 'none';
}
</script>
@endpush
