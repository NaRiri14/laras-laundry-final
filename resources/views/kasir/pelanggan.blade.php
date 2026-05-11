@extends('layouts.kasir')
@section('title', 'Pelanggan')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:25px; color:white; min-height:100vh; background:#0d1117; }
    .card-dark { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px; }
    .search-container { display:flex; gap:10px; margin-bottom:25px; align-items:center; max-width:600px; }
    .search-input { width:100%; padding:12px 15px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:white; outline:none; box-sizing:border-box; }
    .btn-search { background:#00d4aa; color:#0d1117; border:none; padding:12px 25px; border-radius:8px; font-weight:bold; cursor:pointer; }
    .btn-reset { background:#30363d; color:white; border:none; padding:12px 20px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:bold; }
    table { width:100%; border-collapse:collapse; }
    th { text-align:left; color:#8b949e; font-size:11px; padding:15px; border-bottom:2px solid #30363d; text-transform:uppercase; }
    td { padding:15px; border-bottom:1px solid #30363d; font-size:14px; color:#c9d1d9; vertical-align:middle; }
    .btn-wa-group { display:flex; gap:8px; align-items:center; }
    .btn-chat-wa { color:#25d366; text-decoration:none; font-weight:bold; font-size:12px; border:1px solid #25d366; padding:6px 12px; border-radius:6px; }
    .btn-struk-wa { background:#00d4aa; color:#0d1117; padding:7px 15px; border-radius:6px; text-decoration:none; font-size:11px; font-weight:bold; }
    .badge { padding:5px 10px; border-radius:6px; font-size:10px; font-weight:800; border:1px solid; text-transform:uppercase; }
    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:15px !important; }
        table, thead, tbody, th, td, tr { display:block; width:100%; }
        thead { display:none; }
        tr { background:#161b22; border:1px solid #30363d; border-radius:12px; margin-bottom:10px; padding:10px; box-sizing:border-box; }
        td { display:flex; justify-content:space-between; align-items:center; padding:5px 0 !important; border-bottom:1px solid #30363d55 !important; }
        td:last-child { border-bottom:none !important; }
        td::before { content:attr(data-label); font-weight:bold; color:#8b949e; font-size:9px; text-transform:uppercase; width:35%; text-align:left; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div style="margin-bottom:25px;">
        <h2 style="font-family:'Syne'; margin:0; font-size:28px; color:#00d4aa;">🧺 Data Pelanggan Aktif</h2>
        <p style="color:#8b949e; font-size:14px; margin-top:5px;">
            Menampilkan data untuk: <strong style="color:#00d4aa;">{{ $outlet->nama_cabang ?? 'Cabang' }}</strong>
        </p>
    </div>

    <form action="{{ route('pelanggan') }}" method="GET" class="search-container">
        <input type="text" name="cari" class="search-input" placeholder="Cari Nama Pelanggan..." value="{{ $keyword }}">
        <button type="submit" class="btn-search">CARI</button>
        @if($keyword)
        <a href="{{ route('pelanggan') }}" class="btn-reset">RESET</a>
        @endif
    </form>

    <div class="card-dark">
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Nama Pelanggan</th>
                    <th>WhatsApp & Struk</th>
                    <th>Layanan Gabungan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggan as $no => $item)
                @php
                    $p        = $item->pelanggan;
                    $st       = $item->status_cucian;
                    $clr      = ($st == 'Proses') ? '#7c3aed' : '#00d4aa';
                    $namaRapi = ucwords(strtolower($p->nama_pelanggan ?? ''));
                    $noWa     = preg_replace('/[^0-9]/', '', $p->no_hp ?? '');
                    if (substr($noWa, 0, 1) === '0') { $noWa = '62' . substr($noWa, 1); }
                    $cbNama   = $outlet->nama_cabang ?? 'Laras Laundry';

                    // Alamat cabang untuk pesan WA
                    $alamat = "Banjarmasin, Kalimantan Selatan";
                    if($cbNama == "Cabang Pusat") $alamat = "Jl. Merak V, Kelayan Sel., Banjarmasin";
                    elseif($cbNama == "Cabang A")  $alamat = "Jl. Bunga TJ, Kec. Banjarmasin Sel.";
                    elseif($cbNama == "Cabang B")  $alamat = "Jl. Raya Purna Sakti No.34, Banjarmasin Barat";

                    $tgl = now()->format('d/m/y H:i');
                    $line = "------------------------------------------";

                    $pesanStruk = "🧺 *Laras Laundry*\n$alamat\n$line\nTgl : $tgl\n$line\n*$namaRapi*            {$p->no_hp}\n$line\n{$item->semua_layanan}\n$line\n*Total* *Rp " . number_format($item->total_rp, 0, ',', '.') . "*\nStatus                        " . strtoupper($st) . "\n$line\nHP. 0813 5154 3883\nWA. 0821 4812 0213\n--- TERIMA KASIH ---\nSerahkan struk saat ambil cucian";

                    $teksChatWa = "Halo $namaRapi, Kami dari Laras Laundry ingin mengkonfirmasi bahwa laundryan anda";
                @endphp
                <tr>
                    <td data-label="No"><b>{{ $no + 1 }}</b></td>
                    <td data-label="Nama Pelanggan"><b style="color:#fff;">{{ $namaRapi }}</b></td>
                    <td data-label="Aksi">
                        <div>
                            <div class="btn-wa-group">
                                <a href="https://api.whatsapp.com/send?phone={{ $noWa }}&text={{ urlencode($teksChatWa) }}"
                                    target="_blank" class="btn-chat-wa">📱 Chat</a>
                                <a href="https://api.whatsapp.com/send?phone={{ $noWa }}&text={{ urlencode($pesanStruk) }}"
                                    target="_blank" class="btn-struk-wa">📄 Kirim Struk</a>
                            </div>
                            <small style="color:#8b949e; font-size:10px; display:block; margin-top:4px;">📞 {{ $p->no_hp ?? '-' }}</small>
                        </div>
                    </td>
                    <td data-label="Layanan">
                        <span style="color:#3b82f6; font-weight:bold; font-size:13px;">{{ $item->semua_layanan }}</span><br>
                        <small style="color:#8b949e;">Total: Rp {{ number_format($item->total_rp, 0, ',', '.') }}</small>
                    </td>
                    <td data-label="Status">
                        <span class="badge" style="background:{{ $clr }}15; color:{{ $clr }}; border-color:{{ $clr }};">
                            {{ $st }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#8b949e; padding:30px;">
                        Tidak ada data pelanggan aktif.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
