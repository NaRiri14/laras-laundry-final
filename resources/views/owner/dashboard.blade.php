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
    .chart-section { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
    .card-dark { background: #161b22; padding: 20px; border-radius: 15px; border: 1px solid #30363d; }
    .chart-container { position: relative; height: 300px; width: 100%; }
    .layanan-item { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #21262d; }
    .layanan-item:last-child { border-bottom:none; }
    .rank-badge { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px; flex-shrink:0; }
    .tab-switch { display:flex; gap:6px; margin-bottom:18px; }
    .tab-btn {
        background: #0d1117;
        border: 1px solid #30363d;
        color: #8b949e;
        font-size: 12px;
        font-weight: bold;
        padding: 7px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all .15s ease;
    }
    .tab-btn.active { background:#ff9f43; color:#0d1117; border-color:#ff9f43; }
    .tab-pane { display:none; }
    .tab-pane.active { display:block; }
    @media (max-width: 768px) {
        .main-wrapper { margin-left: 0 !important; padding: 15px; }
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
        <div class="header-right" style="text-align:right;">
            <div style="line-height:1.2;">
                <div style="color:#ff9f43; font-weight:bold; font-size:18px;">
                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l') }}
                </div>
                <div style="color:#8b949e; font-size:12px;">
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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <h4 style="margin:0; font-size:14px; color:#8b949e;" id="tabTitle">📊 Kontribusi Laba (%)</h4>
            </div>
            <div class="tab-switch">
                <button type="button" class="tab-btn active" data-tab="kontribusi" onclick="gantiTabRingkasan('kontribusi')">📊 Kontribusi Laba</button>
                <button type="button" class="tab-btn" data-tab="layanan" onclick="gantiTabRingkasan('layanan')">🏆 Layanan Terlaris</button>
            </div>

            <div class="tab-pane active" id="pane-kontribusi">
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

            <div class="tab-pane" id="pane-layanan" style="max-height:280px; overflow-y:auto; padding-right:4px;">
                @forelse($layananTerlaris as $i => $item)
                <div style="margin-bottom:9px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:#c9d1d9;">{{ $item->layanan->nama_layanan ?? 'Layanan #'.$item->id_layanan }}</span>
                        <span style="color:#00d4aa;font-weight:bold;font-size:10px;">{{ $item->total_order }}x</span>
                    </div>
                    <div style="background:#0d1117;height:4px;border-radius:10px;">
                        <div style="width:{{ min(($item->total_order/10)*100,100) }}%;background:#00d4aa;height:100%;border-radius:10px;"></div>
                    </div>
                </div>
                @empty
                <p style="color:#8b949e; text-align:center;">Belum ada transaksi bulan ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function gantiTabRingkasan(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    document.getElementById('pane-' + tab).classList.add('active');

    document.getElementById('tabTitle').innerHTML = (tab === 'kontribusi')
        ? '📊 Kontribusi Laba (%)'
        : '🏆 Layanan Terlaris Bulan Ini';
}
</script>
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
