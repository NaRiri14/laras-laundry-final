@extends('layouts.owner')
@section('title', 'Laporan Cabang')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .main-wrapper { margin-left:260px; padding:20px 25px; color:white; background:#0d1117; min-height:100vh; box-sizing:border-box; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; gap:15px; }
    .filter-bar { display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:15px; background:#161b22; padding:15px; border-radius:12px; border:1px solid #30363d; }
    .filter-bar label { color:#8b949e; font-size:11px; font-weight:bold; display:block; margin-bottom:5px; }
    .filter-bar input[type=text] { background:#0d1117; border:1px solid #30363d; color:white; padding:10px 12px; border-radius:8px; font-size:13px; width:100%; box-sizing:border-box; cursor:pointer; }
    .filter-bar select { background:#0d1117; border:1px solid #30363d; color:white; padding:10px 12px; border-radius:8px; font-size:13px; width:100%; box-sizing:border-box; }
    .grid-summary { display:grid; grid-template-columns:repeat(4,1fr); gap:15px; margin-bottom:15px; }
    .stat-box { background:#161b22; padding:15px; border-radius:12px; border:1px solid #30363d; border-left:4px solid transparent; }
    .stat-box small { color:#8b949e; font-size:10px; font-weight:bold; text-transform:uppercase; }
    .stat-box h3 { margin:5px 0 0 0; font-size:16px; font-family:'Syne'; }
    .card-dark { background:#161b22; padding:15px; border-radius:12px; border:1px solid #30363d; margin-bottom:15px; }
    .chart-container { position:relative; height:200px; width:100%; }
    .table-responsive { width:100%; overflow-x:auto; margin-top:10px; }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    th { text-align:left; color:#8b949e; padding:12px 8px; border-bottom:1px solid #30363d; font-size:11px; text-transform:uppercase; }
    td { padding:12px 8px; border-bottom:1px solid #21262d; vertical-align:middle; }
    .btn-pdf { background:#ff4d4d; color:white; padding:10px 14px; border-radius:8px; border:none; font-weight:bold; font-size:12px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; cursor:pointer; }
    .btn-apply { background:#ff9f43; color:#0d1117; border:none; font-weight:bold; padding:10px 18px; border-radius:8px; cursor:pointer; font-size:13px; white-space:nowrap; }
    .modal { display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.9); align-items:center; justify-content:center; padding:20px; box-sizing:border-box; }
    .modal-img { max-width:90%; max-height:80vh; border-radius:10px; display:block; margin:auto; }
    .close-modal { position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer; z-index:10000; }
    .jatah-bar { height:10px; border-radius:10px; background:#21262d; overflow:hidden; margin-top:6px; }
    .jatah-bar-fill { height:100%; border-radius:10px; transition:width 0.5s; }

    /* Flatpickr dark theme override */
    .flatpickr-calendar { background:#161b22 !important; border:1px solid #30363d !important; border-radius:12px !important; }
    .flatpickr-day { color:#f0f6fc !important; }
    .flatpickr-day:hover { background:#30363d !important; }
    .flatpickr-day.selected { background:#ff9f43 !important; border-color:#ff9f43 !important; color:#0d1117 !important; }
    .flatpickr-day.inRange { background:#30363d !important; border-color:#30363d !important; }
    .flatpickr-months { background:#161b22 !important; }
    .flatpickr-month { color:#f0f6fc !important; fill:#f0f6fc !important; }
    .flatpickr-weekday { color:#8b949e !important; }
    .flatpickr-prev-month, .flatpickr-next-month { color:#f0f6fc !important; fill:#f0f6fc !important; }
    .numInputWrapper input { color:#f0f6fc !important; background:#161b22 !important; }

    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:12px; }
        .filter-bar { grid-template-columns:1fr 1fr; }
        .filter-bar > div:first-child { grid-column:1/-1; }
        .filter-bar > button { grid-column:1/-1; width:100%; }
        .grid-summary { grid-template-columns:1fr 1fr; gap:10px; }
        .stat-box { padding:12px; }
        .stat-box h3 { font-size:13px; }
        .page-header { flex-wrap:wrap; }
        .btn-pdf { width:100%; justify-content:center; }
        .chart-container { height:220px !important; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div class="page-header">
        <div>
            <h2 style="font-family:'Syne'; margin:0; color:#fff;">📍 Laporan Cabang</h2>
            <p style="color:#8b949e; margin-top:2px; font-size:12px;">{{ $namaCabang }}</p>
        </div>
        <button onclick="unduhLaporan()" id="btn_unduh" class="btn-pdf">
            <span>📥</span> UNDUH PDF
        </button>
    </div>

    <form method="GET" action="{{ route('cabang') }}" class="filter-bar">
        <div>
            <label>📍 Cabang</label>
            <select name="cabang">
                @foreach($outlets as $o)
                <option value="{{ $o->id_outlet }}" {{ $cabangSelected == $o->id_outlet ? 'selected' : '' }}>
                    {{ $o->nama_cabang }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>📅 Dari Tanggal</label>
            <input type="text" id="tgl_dari" name="tgl_dari" value="{{ $tglDari }}" placeholder="Pilih tanggal..." readonly>
        </div>
        <div>
            <label>📅 Sampai Tanggal</label>
            <input type="text" id="tgl_sampai" name="tgl_sampai" value="{{ $tglSampai }}" placeholder="Pilih tanggal..." readonly>
        </div>
        <button type="submit" class="btn-apply">🔍 FILTER</button>
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
        <div class="stat-box" style="border-left-color:{{ $labaBersih >= 0 ? '#ff9f43' : '#ef4444' }};">
            <small>LABA BERSIH</small>
            <h3 style="color:{{ $labaBersih >= 0 ? '#ff9f43' : '#ef4444' }};">Rp {{ number_format($labaBersih, 0, ',', '.') }}</h3>
        </div>
        <div class="stat-box" style="border-left-color:{{ $sisaJatah >= 0 ? '#3b82f6' : '#ef4444' }};">
            <small>SISA JATAH MINGGU INI</small>
            <h3 style="color:{{ $sisaJatah >= 0 ? '#3b82f6' : '#ef4444' }};">
                Rp {{ number_format(abs($sisaJatah), 0, ',', '.') }}
                @if($sisaJatah < 0) <span style="font-size:11px;">⚠️</span> @endif
            </h3>
            @if($sisaJatah < 0)
            <div style="color:#ef4444;font-size:10px;font-weight:bold;margin-bottom:4px;">
                Melebihi Rp {{ number_format(abs($sisaJatah),0,',','.') }}
            </div>
            @endif
            @php
                $persen = $jatahTotal > 0 ? min(100, ($pengeluaranMingguIni / $jatahTotal) * 100) : 0;
                $warnaBar = $persen >= 100 ? '#ef4444' : ($persen >= 75 ? '#ff9f43' : '#3b82f6');
            @endphp
            <div class="jatah-bar">
                <div class="jatah-bar-fill" style="width:{{ $persen }}%; background:{{ $warnaBar }};"></div>
            </div>
            <small style="color:#8b949e; font-size:9px;">{{ number_format($persen, 0) }}% dari Rp {{ number_format($jatahTotal, 0, ',', '.') }} (reset tiap minggu)</small>
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
                    <tr><td colspan="4" style="text-align:center; color:#444; padding:20px;">Tidak ada data pengeluaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="myModal" class="modal" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-img" id="imgBukti">
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Date picker
flatpickr("#tgl_dari", {
    locale: "id",
    dateFormat: "Y-m-d",
    maxDate: "today",
    defaultDate: "{{ $tglDari }}",
    onChange: function(selectedDates, dateStr) {
        fpSampai.set('minDate', dateStr);
    }
});
const fpSampai = flatpickr("#tgl_sampai", {
    locale: "id",
    dateFormat: "Y-m-d",
    maxDate: "today",
    defaultDate: "{{ $tglSampai }}",
    minDate: "{{ $tglDari }}"
});

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
    const dari = document.getElementById('tgl_dari').value;
    const sampai = document.getElementById('tgl_sampai').value;
    const cabang = document.querySelector('select[name=cabang]').value;
    window.open(`{{ url('/cetak-laporan') }}?cabang=${cabang}&tgl_dari=${dari}&tgl_sampai=${sampai}`, '_blank');
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
