@extends('layouts.kasir')
@section('title', 'Riwayat Transaksi')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:30px; transition:0.3s; }
    .filter-group { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
    .filter-btn { text-decoration:none; padding:6px 12px; border-radius:6px; font-size:11px; background:#21262d; color:#8b949e; border:1px solid #30363d; }
    .filter-btn.active { background:#00d4aa; color:#0d1117; border-color:#00d4aa; font-weight:bold; }
    .table-wrap { background:#161b22; border:1px solid #30363d; border-radius:15px; padding:20px; overflow-x:auto; }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    th { text-align:left; color:#8b949e; padding:15px; border-bottom:2px solid #30363d; }
    td { padding:15px; border-bottom:1px solid #21262d; }
    @media screen and (max-width:768px) {
        .main-wrapper { margin-left:0; padding:15px; }
        table thead { display:none; }
        table tr { display:block; background:#1c2128; margin-bottom:15px; border:1px solid #30363d; border-radius:12px; padding:10px; }
        table td { display:flex; justify-content:space-between; align-items:center; border:none; padding:8px 10px; text-align:right; }
        table td::before { content:attr(data-label); font-weight:bold; color:#8b949e; text-align:left; font-size:11px; }
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

    <h1 style="font-family:'Syne'; margin-bottom:25px; font-size:28px; color:#00d4aa;">Daftar Cucian</h1>

    <div class="filter-group">
        <a href="{{ route('riwayat', ['filter'=>'Semua']) }}" class="filter-btn {{ $filterStatus == 'Semua' ? 'active' : '' }}">Semua</a>
        <a href="{{ route('riwayat', ['filter'=>'Proses']) }}" class="filter-btn {{ $filterStatus == 'Proses' ? 'active' : '' }}">Proses</a>
        <a href="{{ route('riwayat', ['filter'=>'Selesai']) }}" class="filter-btn {{ $filterStatus == 'Selesai' ? 'active' : '' }}">Selesai</a>
        <a href="{{ route('riwayat', ['filter'=>'Diambil']) }}" class="filter-btn {{ $filterStatus == 'Diambil' ? 'active' : '' }}">Diambil</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:center;">Update</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $row)
                @php
                    $st  = $row->status_cucian;
                    $clr = $st == 'Proses' ? '#7c3aed' : ($st == 'Selesai' ? '#00d4aa' : '#3b82f6');
                @endphp
                <tr>
                    <td data-label="Pelanggan">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <b style="font-size:14px;">{{ $row->pelanggan->nama_pelanggan ?? '-' }}</b>
                            <a href="javascript:void(0)"
                                onclick="editPelanggan('{{ $row->pelanggan->id_pelanggan }}','{{ $row->pelanggan->nama_pelanggan }}','{{ $row->pelanggan->no_hp }}')"
                                style="text-decoration:none; font-size:12px;">✏️</a>
                        </div>
                        <div style="color:#00d4aa; font-size:11px; margin-top:2px;">{{ $row->pelanggan->no_hp ?? '-' }}</div>
                        <div style="color:#8b949e; font-size:10px; margin-top:2px;">{{ \Carbon\Carbon::parse($row->tgl_masuk)->format('d M Y') }}</div>
                        @if($row->catatan)
                        <div style="font-size:10px; color:#f0ad4e; margin-top:5px; font-style:italic;">📝 {{ $row->catatan }}</div>
                        @endif
                    </td>
                    <td data-label="Layanan" style="color:#3b82f6; font-weight:bold;">
                        {{ $row->layanan->nama_layanan ?? '-' }}
                    </td>
                    <td data-label="Total" style="color:#00d4aa; font-weight:800;">
                        Rp {{ number_format($row->total_bayar, 0, ',', '.') }}
                    </td>
                    <td data-label="Status">
                        <span style="background:{{ $clr }}15; color:{{ $clr }}; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:bold;">
                            {{ $st }}
                        </span>
                        {{-- Tampilkan waktu pengambilan jika status Diambil --}}
                        @if($st == 'Diambil')
                        <div style="font-size:9px; color:#8b949e; margin-top:5px;">
                            📅 {{ \Carbon\Carbon::parse($row->updated_at)->format('d/m/y H:i') }}
                        </div>
                        @endif
                    </td>
                    <td data-label="Update" style="text-align:center;">
                        @if($st == 'Proses')
                        <a href="{{ route('riwayat.update', ['update_id'=>$row->id_transaksi,'status_baru'=>'Selesai','filter'=>$filterStatus]) }}"
                            style="color:#00d4aa; text-decoration:none; font-size:11px; border:1px solid #30363d; padding:5px 8px; border-radius:5px;">
                            Selesai ✅
                        </a>
                        @elseif($st == 'Selesai')
                        <div style="display:flex; flex-direction:column; gap:5px; align-items:center;">
                            <a href="{{ route('riwayat.update', ['update_id'=>$row->id_transaksi,'status_baru'=>'Proses','filter'=>$filterStatus]) }}"
                                style="color:#ef4444; font-size:10px; text-decoration:none;">✖ Salah Klik?</a>
                            <a href="{{ route('riwayat.update', ['update_id'=>$row->id_transaksi,'status_baru'=>'Diambil','filter'=>$filterStatus]) }}"
                                onclick="return confirm('Konfirmasi: Pakaian milik {{ $row->pelanggan->nama_pelanggan }} ({{ $row->layanan->nama_layanan }}) sudah diambil?')"
                                style="color:#3b82f6; text-decoration:none; font-size:11px; border:1px solid #30363d; padding:5px 8px; border-radius:5px;">
                                Diambil 🧺
                            </a>
                        </div>
                        @else
                        <span style="color:#8b949e; font-size:11px;">Sudah Diambil ✔</span>
                        @endif
                    </td>
                    <td data-label="Aksi" style="text-align:center;">
                        <a href="javascript:void(0)"
                            onclick="silentPrint('{{ $row->id_transaksi }}','{{ $row->total_bayar }}')"
                            style="text-decoration:none; margin-right:10px;">🖨️</a>
                        <a href="{{ route('riwayat.hapus', $row->id_transaksi) }}"
                            onclick="return confirm('Hapus?')"
                            style="text-decoration:none; color:#ef4444;">🗑️</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#8b949e; padding:30px;">
                        Tidak ada data transaksi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- FORM EDIT PELANGGAN --}}
<form id="fEditPelanggan" method="POST" action="{{ route('riwayat.edit_pelanggan') }}" style="display:none;">
    @csrf
    <input type="hidden" name="id_pelanggan" id="id_pel">
    <input type="hidden" name="nama_baru" id="nm_pel">
    <input type="hidden" name="hp_baru" id="hp_pel">
</form>
@endsection

@push('scripts')
<script>
function silentPrint(id, bayar) {
    var iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = '{{ route("cetak.struk") }}?id=' + id + '&bayar=' + bayar + '&mode=silent';
    document.body.appendChild(iframe);
    iframe.onload = function() {
        iframe.contentWindow.print();
        setTimeout(function() {
            document.body.removeChild(iframe);
        }, 1000);
    };
}

function editPelanggan(id, nama, hp) {
    let n = prompt("Edit Nama Pelanggan:", nama);
    if (n == null || n == "") return;
    let h = prompt("Edit No. HP:", hp);
    if (h == null || h == "") return;
    document.getElementById('id_pel').value = id;
    document.getElementById('nm_pel').value = n;
    document.getElementById('hp_pel').value = h;
    document.getElementById('fEditPelanggan').submit();
}
</script>
@endpush
