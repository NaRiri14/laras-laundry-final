@extends('layouts.owner')
@section('title', 'Laporan Cabang')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:20px 25px; color:white; background:#0d1117; min-height:100vh; box-sizing:border-box; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; gap:15px; }
    .filter-bar { display:flex; gap:15px; margin-bottom:15px; background:#161b22; padding:10px 15px; border-radius:12px; border:1px solid #30363d; }
    .grid-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:15px; margin-bottom:15px; }
    .stat-box { background:#161b22; padding:15px; border-radius:12px; border:1px solid #30363d; border-left:4px solid transparent; }
    .stat-box small { color:#8b949e; font-size:10px; font-weight:bold; text-transform:uppercase; }
    .stat-box h3 { margin:5px 0 0 0; font-size:18px; font-family:'Syne'; }
    .card-dark { background:#161b22; padding:15px; border-radius:12px; border:1px solid #30363d; margin-bottom:15px; }
    .chart-container { position:relative; height:200px; width:100%; }
    .table-responsive { width:100%; overflow-x:auto; margin-top:10px; }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    th { text-align:left; color:#8b949e; padding:12px 8px; border-bottom:1px solid #30363d; font-size:11px; text-transform:uppercase; }
    td { padding:12px 8px; border-bottom:1px solid #21262d; vertical-align:middle; }
    .btn-pdf { background:#ff4d4d; color:white; padding:8px 12px; border-radius:8px; border:none; font-weight:bold; font-size:11px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; cursor:pointer; }
    .btn-apply { background:#ff9f43; color:#0d1117; border:none; font-weight:bold; padding:8px 12px; border-radius:8px; cursor:pointer; }
    select { background:#0d1117; border:1px solid #30363d; color:white; padding:8px; border-radius:8px; }
    .modal { display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.9); align-items:center; justify-content:center; padding:20px; box-sizing:border-box; }
    .modal-img { max-width:90%; max-height:80vh; border-radius:10px; display:block; margin:auto; }
    .close-modal { position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer; z-index:10000; }
    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:15px; }
        .grid-summary { grid-template-columns:1fr; }
        .filter-bar { flex-direction:column; }
        .chart-container { height:250px !important; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h2 style="font-family:'Syne'; margin:0; color:#fff;">📍 Laporan {{ ucfirst($filterWaktu) }}</h2>
            <p style="color:#8b949e; margin-top:2px; font-size:12px;">{{ $namaCabang }}</p>
        </div>
        <button onclick="unduhLaporan()" id="btn_unduh" class="btn-pdf">
            <span>📥</span> UNDUH PDF
        </button>
    </div>

    <form method="GET" action="{{ route('cabang') }}" class="filter-bar">
        <div style="display:flex; gap:10px; width:100%;">
            <select name="cabang" style="flex:1;">
                @foreach($outlets as $o)
                <option value="{{ $o->id_outlet }}" {{ $cabangSelected == $o->id_outlet ? 'selected' : '' }}>
                    {{ $o->nama_cabang }}
                </option>
                @endforeach
            </select>
            <select name="waktu" style="flex:1;">
                <option value="hari" {{ $filterWaktu == 'hari' ? 'selected' : '' }}>Harian</option>
                <option value="minggu" {{ $filterWaktu == 'minggu' ? 'selected' : '' }}>Mingguan</option>
                <option value="bulan" {{ $filterWaktu == 'bulan' ? 'selected' : '' }}>Bulanan</option>
            </select>
            <button type="submit" class="btn-apply">FILTER</button>
        </div>
    </form>

    <div class="grid-summary">
        <div class="stat-box" style="border-left-color:#00d4aa;">
            <small>PEMASUKAN</small>
            <h3 style="color:#00d4aa;">Rp {{ number_format($totalIn, 0, ',', '.') }}</h3>
        </div>
        <div class="stat-box" style="border-left-color:#ff4d4d;">
            <small>PENGELUARAN</small>
            <h3 style="color:#ff4d4d;">Rp {{ number_format($totalOut, 0, ',', '.') }}</h3>
        </div>
        <div class="stat-box" style="border-left-color:#ff9f43;">
            <small>LABA BERSIH</small>
            <h3 style="color:#ff9f43;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="card-dark">
        <h4 style="margin:0 0 15px 0; font-size:14px; color:#8b949e;">📈 Grafik Perbandingan</h4>
        <div class="chart-container"><canvas id="chartCompare"></canvas></div>
    </div>

    <div class="card-dark">
        <h4 style="margin:0 0 15px 0; font-size:14px; color:#8b949e;">💸 Rincian Bukti Pengeluaran</h4>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tgl</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                        <th>Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengeluaranList as $row)
                    <tr>
                        <td style="color:#8b949e;">{{ \Carbon\Carbon::parse($row->tgl_pengeluaran)->format('d/m') }}</td>
                        <td>{{ $row->keterangan }}</td>
                        <td style="color:#ff4d4d; font-weight:bold;">Rp {{ number_format($row->jumlah, 0, ',', '.') }}</td>
                        <td>
                            @if($row->foto_bukti)
                            <button class="btn-apply" style="padding:5px 10px; font-size:11px;"
                                onclick="openModal('{{ asset('img_bukti/'.$row->foto_bukti) }}')">
                                📸 LIHAT
                            </button>
                            @else
                            <small style="color:#444;">N/A</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:#444;">Tidak ada data pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL FOTO --}}
<div id="myModal" class="modal" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-img" id="imgBukti">
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function openModal(src) {
    document.getElementById('myModal').style.display = "flex";
    document.getElementById('imgBukti').src = src;
}
function closeModal() {
    document.getElementById('myModal').style.display = "none";
}
function unduhLaporan() {
    const btn = document.getElementById('btn_unduh');
    btn.innerHTML = "⌛ PROSES...";
    window.open('{{ route("cetak.laporan") }}?cabang={{ $cabangSelected }}&waktu={{ $filterWaktu }}', '_blank');
    setTimeout(() => { btn.innerHTML = "<span>📥</span> UNDUH PDF"; }, 3000);
}

new Chart(document.getElementById('chartCompare').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($labels) !!},
        datasets: [
            { label:'Pemasukan', data:{!! json_encode($dataOmzet) !!}, backgroundColor:'#00d4aa', borderRadius:5 },
            { label:'Pengeluaran', data:{!! json_encode($dataPengeluaran) !!}, backgroundColor:'#ff4d4d', borderRadius:5 }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins: { legend: { labels: { color:'#8b949e', font:{ size:10 } } } },
        scales: {
            y: { grid:{ color:'#30363d' }, ticks:{ color:'#8b949e', font:{ size:10 } } },
            x: { ticks:{ color:'#8b949e', font:{ size:10 } } }
        }
    }
});
</script>
@endpush
