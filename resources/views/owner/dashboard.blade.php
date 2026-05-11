@extends('layouts.owner')
@section('title', 'Dashboard Owner')

@push('styles')
<style>
    .main-wrapper {
        margin-left: 260px;
        padding: 25px 30px;
        color: white;
        background: #0d1117;
        min-height: 100vh;
        box-sizing: border-box;
    }
    .header-flex {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        gap: 15px;
    }
    .header-right { text-align: right; }
    .btn-pdf {
        background: #ff4d4d;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: bold;
        text-decoration: none;
        font-size: 11px;
        margin-top: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .grid-top { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
    .card-stat { background: #161b22; padding: 20px; border-radius: 15px; border: 1px solid #30363d; }
    .card-stat h2 { margin: 10px 0 0 0; font-size: 22px; font-family: 'Syne'; }
    .chart-section { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .card-dark { background: #161b22; padding: 20px; border-radius: 15px; border: 1px solid #30363d; }
    .chart-container { position: relative; height: 300px; width: 100%; }
    @media (max-width: 768px) {
        .main-wrapper { margin-left: 0 !important; padding: 15px; }
        .header-left h1 { font-size: 15px !important; max-width: 160px; line-height: 1.4; }
        .header-left p { font-size: 10px !important; }
        .header-right .day { font-size: 14px !important; }
        .header-right .date { font-size: 10px !important; }
        .btn-pdf { padding: 6px 10px; font-size: 10px; }
        .grid-top { grid-template-columns: 1fr; gap: 15px; }
        .chart-section { grid-template-columns: 1fr; }
        .chart-container { height: 250px !important; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div class="header-flex">
        <div class="header-left">
            <h1 style="font-family:'Syne'; margin:0; font-size:22px;">Halo, Owner Laras Laundry 👋</h1>
            <p style="color:#8b949e; margin:4px 0 0 0; font-size:13px;">Laras Laundry Global</p>
        </div>
        <div class="header-right">
            <div style="line-height:1.2;">
                <div class="day" style="color:#ff9f43; font-weight:bold; font-size:18px;">
                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l') }}
                </div>
                <div class="date" style="color:#8b949e; font-size:12px;">
                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </div>
            </div>
            <a href="{{ route('cetak.laporan_global') }}" target="_blank" class="btn-pdf">
                <span>📥</span> UNDUH LAPORAN
            </a>
        </div>
    </div>

    <div class="grid-top">
        <div class="card-stat" style="border-bottom:4px solid #00d4aa;">
            <small style="color:#8b949e; font-size:10px; font-weight:bold; text-transform:uppercase;">Total Omzet</small>
            <h2 style="color:#00d4aa;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h2>
        </div>
        <div class="card-stat" style="border-bottom:4px solid #ff4d4d;">
            <small style="color:#8b949e; font-size:10px; font-weight:bold; text-transform:uppercase;">Total Pengeluaran</small>
            <h2 style="color:#ff4d4d;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h2>
        </div>
        <div class="card-stat" style="border-bottom:4px solid #3b82f6;">
            <small style="color:#8b949e; font-size:10px; font-weight:bold; text-transform:uppercase;">Laba Bersih</small>
            <h2 style="color:#3b82f6;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</h2>
        </div>
    </div>

    <div class="chart-section">
        <div class="card-dark">
            <h4 style="margin:0 0 20px 0; font-size:14px; color:#8b949e;">📈 Perbandingan Laba Per Cabang</h4>
            <div class="chart-container">
                <canvas id="chartCabangComp"></canvas>
            </div>
        </div>

        <div class="card-dark">
            <h4 style="margin:0 0 20px 0; font-size:14px; color:#8b949e;">📊 Kontribusi Laba (%)</h4>
            <div class="progress-list">
                @php $colors = ['#3b82f6','#a855f7','#ec4899','#00d4aa','#f59e0b']; @endphp
                @foreach($cabangList as $index => $cabang)
                @php
                    $percent = ($labaBersih > 0) ? ($labaPerCabang[$index] / $labaBersih) * 100 : 0;
                    if($percent < 0) $percent = 0;
                @endphp
                <div style="margin-bottom:18px;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                        <span>{{ $cabang }}</span>
                        <span style="font-weight:bold;">{{ number_format($percent, 1) }}%</span>
                    </div>
                    <div style="width:100%; height:6px; background:#0d1117; border-radius:10px; overflow:hidden;">
                        <div style="width:{{ $percent }}%; height:100%; background:{{ $colors[$index % 5] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chartCabangComp').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($cabangList) !!},
        datasets: [{
            label: 'Laba Bersih',
            data: {!! json_encode($labaPerCabang) !!},
            backgroundColor: ['#3b82f6','#a855f7','#ec4899','#00d4aa','#f59e0b'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#30363d' },
                ticks: {
                    color: '#8b949e',
                    font: { size: 10 },
                    callback: val => 'Rp ' + val.toLocaleString('id-ID')
                }
            },
            x: {
                ticks: { color: '#8b949e', font: { size: 9 }, maxRotation: 45 },
                grid: { display: false }
            }
        }
    }
});
</script>
@endpush
