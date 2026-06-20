@extends('layouts.kasir')
@section('title', 'Laporan Pendapatan')

@push('styles')
<style>
    body { font-family:'Inter', sans-serif; background:#0d1117; margin:0; }
    .main-wrapper { margin-left:260px; padding:30px; color:white; min-height:100vh; box-sizing:border-box; }
    .card-dark { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px; margin-bottom:25px; }
    .stats-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:20px; margin:20px 0; }
    .stat-box { background:#161b22; border:1px solid #30363d; padding:20px; border-radius:12px; min-width:0; }
    .stat-box small { color:#8b949e; font-size:10px; text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:10px; }
    .stat-box h2 { margin:0; font-size:clamp(14px,4vw,20px); font-family:'Syne'; line-height:1.25; word-break:break-word; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .jatah-bar { height:8px; border-radius:10px; background:#0d1117; overflow:hidden; margin-top:10px; }
    .jatah-bar-fill { height:100%; border-radius:10px; transition:width 0.5s; }
    .filter-bar { display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; margin-bottom:20px; background:#161b22; padding:15px; border-radius:12px; border:1px solid #30363d; }
    .filter-bar label { color:#8b949e; font-size:11px; font-weight:bold; display:block; margin-bottom:4px; }
    .filter-bar input[type=date] { background:#0d1117; border:1px solid #30363d; color:white; padding:8px 12px; border-radius:8px; font-size:13px; color-scheme:dark; cursor:pointer; }
    .btn-filter { background:#00d4aa; color:#0d1117; border:none; font-weight:bold; padding:9px 18px; border-radius:8px; cursor:pointer; font-size:13px; }
    .table-responsive { width:100%; overflow-x:auto; }
    table { width:100%; border-collapse:collapse; min-width:500px; }
    th { text-align:left; color:#8b949e; font-size:11px; padding:15px 10px; border-bottom:2px solid #30363d; text-transform:uppercase; }
    td { padding:15px 10px; border-bottom:1px solid #30363d; font-size:13px; color:#f0f6fc; }
    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:15px !important; }
        .stats-grid { grid-template-columns:1fr !important; gap:8px !important; }
        .stat-box { padding:12px !important; }
        .stat-box h2 { font-size:18px !important; white-space:normal !important; }
        .filter-bar { flex-direction:column; align-items:stretch !important; }
        .filter-bar input[type=date] { width:100%; box-sizing:border-box; }
        .table-responsive { overflow-x:auto; }
        table { min-width:unset !important; }
        th, td { padding:8px 4px !important; font-size:11px !important; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div style="margin-bottom:20px;">
        <h2 style="margin:0; color:#00d4aa;">Laporan Keuangan</h2>
        <p style="color:#8b949e; font-size:13px; margin-top:5px;">Pilih rentang tanggal untuk melihat laporan.</p>
    </div>

    <form method="GET" action="{{ route('laporan') }}" class="filter-bar">
        <div>
            <label>Dari Tanggal</label>
            <input type="date" name="tgl_dari" value="{{ $tglDari }}" max="{{ now()->toDateString() }}">
        </div>
        <div>
            <label>Sampai Tanggal</label>
            <input type="date" name="tgl_sampai" value="{{ $tglSampai }}" max="{{ now()->toDateString() }}">
        </div>
        <button type="submit" class="btn-filter">🔍 FILTER</button>
    </form>

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
            <h2 style="color:{{ $labaBersih >= 0 ? '#00d4aa' : '#ef4444' }};">Rp {{ number_format($labaBersih,0,',','.') }}</h2>
        </div>
        <div class="stat-box">
            <small>Transaksi</small>
            <h2 style="color:#ff9f43;">{{ $totalTransaksi }} <span style="font-size:12px; color:#8b949e;">Order</span></h2>
        </div>
        <div class="stat-box" style="border-left:3px solid {{ $sisaJatah >= 0 ? '#3b82f6' : '#ef4444' }};">
            <small>Sisa Jatah Minggu Ini</small>
            <h2 style="color:{{ $sisaJatah >= 0 ? '#3b82f6' : '#ef4444' }};">
                Rp {{ number_format(abs($sisaJatah),0,',','.') }}
                @if($sisaJatah < 0) <span style="font-size:11px;">⚠️</span> @endif
            </h2>
            @if($sisaJatah < 0)
            <div style="color:#ef4444;font-size:10px;font-weight:bold;margin-bottom:4px;">
                Melebihi Rp {{ number_format(abs($sisaJatah),0,',','.') }}
            </div>
            @endif
            @php
                $persenJatahLap = $jatahTotal > 0 ? min(100, ($pengeluaranMingguIni / $jatahTotal) * 100) : 0;
                $warnaBarLap = $persenJatahLap >= 100 ? '#ef4444' : ($persenJatahLap >= 75 ? '#ff9f43' : '#3b82f6');
            @endphp
            <div class="jatah-bar">
                <div class="jatah-bar-fill" style="width:{{ $persenJatahLap }}%; background:{{ $warnaBarLap }};"></div>
            </div>
            <small style="color:#8b949e; font-size:9px; margin-top:6px; text-transform:none;">{{ number_format($persenJatahLap, 0) }}% dari Rp {{ number_format($jatahTotal,0,',','.') }}</small>
        </div>
    </div>

    <div class="card-dark">
        <h4 style="margin:0 0 20px 0; color:#8b949e; font-size:11px; letter-spacing:1px;">📊 GRAFIK PENDAPATAN</h4>
        <div style="height:250px;">
            <canvas id="chartLaporan"></canvas>
        </div>
    </div>

    <div class="card-dark">
        <h4 style="margin:0 0 20px 0; color:#8b949e; font-size:11px; letter-spacing:1px;">🕒 RINCIAN TRANSAKSI</h4>
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
