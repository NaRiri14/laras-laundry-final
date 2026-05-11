@extends('layouts.kasir')
@section('title', 'Laporan Pendapatan')

@push('styles')
<style>
    body { font-family:'Inter', sans-serif; background:#0d1117; margin:0; }
    .main-wrapper { margin-left:260px; padding:30px; color:white; min-height:100vh; box-sizing:border-box; }
    .card-dark { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px; margin-bottom:25px; }
    .btn-pill { padding:8px 18px; border-radius:20px; border:1px solid #30363d; color:#8b949e; text-decoration:none; font-size:13px; margin-right:5px; margin-bottom:10px; transition:0.3s; display:inline-block; }
    .btn-pill.active { background:#00d4aa !important; color:#0d1117 !important; font-weight:bold; border-color:#00d4aa !important; }
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin:20px 0; }
    .stat-box { background:#161b22; border:1px solid #30363d; padding:20px; border-radius:12px; }
    .stat-box small { color:#8b949e; font-size:10px; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:10px; }
    .stat-box h2 { margin:0; font-size:22px; font-family:'Syne'; }
    .table-responsive { width:100%; overflow-x:auto; }
    table { width:100%; border-collapse:collapse; min-width:500px; }
    th { text-align:left; color:#8b949e; font-size:11px; padding:15px 10px; border-bottom:2px solid #30363d; text-transform:uppercase; }
    td { padding:15px 10px; border-bottom:1px solid #30363d; font-size:13px; color:#f0f6fc; }
    @media (max-width:768px) {
    .main-wrapper { margin-left:0 !important; padding:15px !important; }
    .stats-grid { grid-template-columns:1fr !important; gap:8px !important; }
    .stat-box { padding:12px !important; }
    .stat-box h2 { font-size:16px !important; }

    /* Tabel muat 1 layar tanpa scroll */
    .table-responsive { overflow-x:hidden !important; }
    table { min-width:unset !important; }
    th, td { padding:8px 4px !important; font-size:7px !important; white-space:nowrap; }
}
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div style="margin-bottom:30px;">
        <h2 style="margin:0; color:#00d4aa;">Laporan Keuangan</h2>
        <p style="color:#8b949e; font-size:13px; margin-top:5px;">Pantau arus kas masuk dan keluar secara real-time.</p>
    </div>

    <div style="margin-bottom:25px;">
        <a href="?period=hari" class="btn-pill {{ $filter == 'hari' ? 'active' : '' }}">Hari Ini</a>
        <a href="?period=minggu" class="btn-pill {{ $filter == 'minggu' ? 'active' : '' }}">Minggu Ini</a>
        <a href="?period=bulan" class="btn-pill {{ $filter == 'bulan' ? 'active' : '' }}">Bulan Ini</a>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <small>Pendapatan</small>
            <h2 style="color:#00d4aa;">Rp {{ number_format($totalPendapatan,0,',','.') }}</h2>
        </div>
        <div class="stat-box">
            <small>Pengeluaran</small>
            <h2 style="color:#ef4444;">Rp {{ number_format($totalPengeluaran,0,',','.') }}</h2>
        </div>
        <div class="stat-box">
            <small>Laba Bersih</small>
            <h2 style="color:#00d4aa;">Rp {{ number_format($labaBersih,0,',','.') }}</h2>
        </div>
        <div class="stat-box">
            <small>Transaksi</small>
            <h2 style="color:#ff9f43;">{{ $totalTransaksi }} <span style="font-size:12px; color:#8b949e;">Order</span></h2>
        </div>
    </div>

    <div class="card-dark">
        <h4 style="margin:0 0 20px 0; color:#8b949e; font-size:11px; letter-spacing:1px;">📊 GRAFIK PENDAPATAN</h4>
        <div style="height:250px;">
            <canvas id="chartLaporan"></canvas>
        </div>
    </div>

    <div class="card-dark">
        <div style="margin-bottom:20px;">
            <h4 style="margin:0; color:#8b949e; font-size:11px; letter-spacing:1px;">🕒 RINCIAN TRANSAKSI</h4>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiList as $t)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($t->tgl_masuk)->format('d/m/y H:i') }}</td>
                        <td style="font-weight:bold; color:#fff;">{{ $t->pelanggan->nama_pelanggan ?? '-' }}</td>
                        <td style="color:#3b82f6;">{{ $t->layanan->nama_layanan ?? '-' }}</td>
                        <td style="color:#00d4aa; font-weight:bold;">Rp {{ number_format($t->total_bayar,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#8b949e; padding:20px;">Tidak ada data transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chartLaporan'), {
    type: 'line',
    data: {
        labels: {!! json_encode($labelGrafik) !!},
        datasets: [{
            label: 'Pendapatan',
            data: {!! json_encode($dataNilai) !!},
            borderColor: '#00d4aa',
            backgroundColor: 'rgba(0,212,170,0.1)',
            fill: true, tension: 0.4,
            pointRadius: 4, pointBackgroundColor: '#00d4aa'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#30363d' },
                ticks: { color: '#8b949e', font: { size: 10 }, callback: val => 'Rp ' + val.toLocaleString('id-ID') }
            },
            x: { grid: { display: false }, ticks: { color: '#8b949e', font: { size: 10 } } }
        }
    }
});
</script>
@endpush
