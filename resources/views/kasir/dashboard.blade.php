@extends('layouts.kasir')
@section('title', 'Dashboard')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:25px; color:white; }
    .box-card { background:#161b22; border:1px solid #30363d; padding:20px; border-radius:12px; margin-bottom:20px; min-width:0; }
    .item-transaksi { background:#0d1117; padding:15px; border-radius:10px; border-left:4px solid #00d4aa; display:flex; justify-content:space-between; align-items:center; }
    .grid-header { display:grid; grid-template-columns:repeat(5,1fr); gap:15px; margin-bottom:20px; }
    .nilai-box { color:#00d4aa; font-size:clamp(13px,3.5vw,18px); font-weight:bold; margin-top:8px; line-height:1.25; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .jatah-bar { height:8px; border-radius:10px; background:#0d1117; overflow:hidden; margin-top:10px; }
    .jatah-bar-fill { height:100%; border-radius:10px; transition:width 0.5s; }
    .grid-tengah { display:grid; grid-template-columns:1.5fr 1fr; gap:20px; margin-bottom:20px; }
    .container-transaksi { display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px; }

    @media(max-width:768px){
        .main-wrapper { margin-left:0 !important; padding:15px !important; }
        .grid-header { grid-template-columns:1fr !important; gap:8px !important; }
        .grid-tengah { grid-template-columns:1fr !important; }
        .container-transaksi { grid-template-columns:1fr !important; }
        .box-card { padding:12px !important; margin-bottom:8px !important; }
        .nilai-box { font-size:18px !important; white-space:normal !important; }
        .item-transaksi { flex-direction:column; align-items:flex-start; gap:5px; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:10px;">
        <h1 style="font-family:'Syne'; margin:0; font-size:24px; color:#00d4aa;">Dashboard</h1>
        <div style="text-align:right;">
            <div style="color:#00d4aa; font-weight:bold; font-size:14px;">
                {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l') }}
            </div>
            <div style="color:#8b949e; font-size:11px;">
                {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <div class="grid-header">
        <div class="box-card">
            <small style="color:#8b949e;font-size:11px;">ORDER</small>
            <div class="nilai-box">{{ $totalOrder }}</div>
        </div>
        <div class="box-card">
            <small style="color:#8b949e;font-size:11px;">MASUK</small>
            <div class="nilai-box">Rp {{ number_format($pemasukan,0,',','.') }}</div>
        </div>
        <div class="box-card">
            <small style="color:#8b949e;font-size:11px;">KELUAR</small>
            <div class="nilai-box" style="color:#ef4444;">Rp {{ number_format($pengeluaran,0,',','.') }}</div>
        </div>
        <div class="box-card">
            <small style="color:#8b949e;font-size:11px;">LABA BERSIH</small>
            <div class="nilai-box" style="color:{{ $labaBersih >= 0 ? '#00d4aa' : '#ef4444' }};">Rp {{ number_format($labaBersih,0,',','.') }}</div>
        </div>
        <div class="box-card" style="border-left:3px solid {{ $sisaJatah >= 0 ? '#3b82f6' : '#ef4444' }};">
            <small style="color:#8b949e;font-size:11px;">SISA JATAH MINGGU INI</small>
            <div class="nilai-box" style="color:{{ $sisaJatah >= 0 ? '#3b82f6' : '#ef4444' }};">
                Rp {{ number_format(abs($sisaJatah),0,',','.') }}
                @if($sisaJatah < 0) <span style="font-size:10px;">⚠️</span> @endif
            </div>
            @if($sisaJatah < 0)
            <div style="color:#ef4444;font-size:10px;margin-top:4px;font-weight:bold;">
                Melebihi Rp {{ number_format(abs($sisaJatah),0,',','.') }}
            </div>
            @endif
            @php
                $persenJatahDash = $jatahTotal > 0 ? min(100, ($pengeluaranMingguIni / $jatahTotal) * 100) : 0;
                $warnaBarDash = $persenJatahDash >= 100 ? '#ef4444' : ($persenJatahDash >= 75 ? '#ff9f43' : '#3b82f6');
            @endphp
            <div class="jatah-bar">
                <div class="jatah-bar-fill" style="width:{{ $persenJatahDash }}%; background:{{ $warnaBarDash }};"></div>
            </div>
        </div>
    </div>

    <div class="grid-tengah">
        <div class="box-card">
            <small style="color:#8b949e;font-size:11px;font-weight:bold;">📊 PENDAPATAN 7 HARI</small>
            <div style="height:220px;margin-top:20px;"><canvas id="grafik"></canvas></div>
        </div>
        <div class="box-card">
            <small style="color:#8b949e;font-size:11px;font-weight:bold;">🏆 LAYANAN TERLARIS</small>
            <div style="margin-top:20px;">
                @forelse($layananTerlaris as $l)
                <div style="margin-bottom:15px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                        <span>{{ $l->layanan->nama_layanan ?? '-' }}</span>
                        <span style="color:#00d4aa;font-weight:bold;">{{ $l->jml }}x</span>
                    </div>
                    <div style="background:#0d1117;height:6px;border-radius:10px;">
                        <div style="width:{{ min(($l->jml/10)*100,100) }}%;background:#00d4aa;height:100%;border-radius:10px;"></div>
                    </div>
                </div>
                @empty
                <p style="color:#8b949e;font-size:13px;">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="box-card">
        <small style="color:#8b949e;font-size:11px;font-weight:bold;">🕒 TRANSAKSI TERBARU</small>
        <div class="container-transaksi">
            @forelse($transaksiTerbaru as $t)
            <div class="item-transaksi">
                <div style="font-weight:600;font-size:14px;">{{ $t->pelanggan->nama_pelanggan ?? '-' }}</div>
                <div style="color:#00d4aa;font-weight:bold;">Rp {{ number_format($t->total_bayar,0,',','.') }}</div>
            </div>
            @empty
            <p style="color:#8b949e;">Belum ada transaksi.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('grafik'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($labelHari) !!},
        datasets: [{ data: {!! json_encode($dataPendapatan) !!}, backgroundColor: '#00d4aa', borderRadius: 4 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#30363d' }, ticks: { color: '#8b949e', font: { size: 10 } } },
            x: { grid: { display: false }, ticks: { color: '#8b949e', font: { size: 10 } } }
        }
    }
});
</script>
@endpush
