<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title') - Laras Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0d1117;
            --card-dark: #161b22;
            --border: #30363d;
            --text-gray: #8b949e;
            --accent: #00d4aa;
            --danger: #ff4d4d;
        }
        body {
            background-color: var(--bg-dark);
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            overflow-x: hidden;
        }
        .sidebar {
            width: 260px; height: 100vh;
            background: var(--bg-dark);
            border-right: 1px solid var(--border);
            position: fixed;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1100;
            transition: transform 0.3s ease;
        }
        .logo-area { padding: 0 25px 15px; display: flex; align-items: center; gap: 10px; }
        .logo-area h2 { font-family: 'Syne'; margin: 0; color: var(--accent); font-size: 22px; }
        .info-cabang {
            padding: 15px; margin: 0 20px 25px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px; border: 1px solid var(--border);
        }
        .info-cabang small { color: var(--accent); font-size: 9px; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 6px; }
        .info-cabang p.nama-cabang { margin: 0; font-size: 14px; color: #fff; font-weight: 600; }
        .info-cabang p.alamat-detail { margin: 6px 0 0 0; font-size: 11px; color: var(--text-gray); line-height: 1.5; }
        .sidebar a { display: flex; align-items: center; padding: 12px 25px; color: var(--text-gray); text-decoration: none; font-size: 14px; transition: 0.3s; gap: 12px; }
        .sidebar a.active { background: rgba(0,212,170,0.1) !important; color: var(--accent) !important; border-right: 3px solid var(--accent); font-weight: 600; }
        .menu-label { padding: 0 25px; font-size: 10px; color: #484f58; display: block; margin-bottom: 10px; }
        .mobile-header {
            display: none;
            background: var(--bg-dark);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 1050;
            justify-content: space-between; align-items: center;
        }
        .btn-menu { font-size: 24px; color: var(--accent); cursor: pointer; }
        @media screen and (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0 !important; padding: 15px !important; width: 100% !important; box-sizing: border-box; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="mobile-header">
    <div style="font-family:'Syne'; color:var(--accent); font-weight:bold;">🧺 LARAS LAUNDRY</div>
    <div class="btn-menu" onclick="toggleSidebar()">☰</div>
</div>

<div class="sidebar" id="mySidebar">
    <div class="logo-area">
        <span>🧺</span>
        <h2>Laras Laundry</h2>
    </div>

    <div class="info-cabang">
        <small>📍 LOKASI TEMPAT</small>
        <p class="nama-cabang">{{ $outlet->nama_cabang ?? 'Cabang Pusat' }}</p>
        <p class="alamat-detail">{{ $outlet->alamat_outlet ?? 'Alamat belum diatur.' }}</p>
    </div>

    <div class="menu-group">
        <span class="menu-label">UTAMA</span>
        <a href="{{ route('kasir.dashboard') }}" class="{{ request()->routeIs('kasir.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
        <a href="{{ route('kasir') }}" class="{{ request()->routeIs('kasir') ? 'active' : '' }}">📄 Kasir</a>
    </div>
    <div class="menu-group">
        <span class="menu-label">KEUANGAN</span>
        <a href="{{ route('laporan') }}" class="{{ request()->routeIs('laporan') ? 'active' : '' }}">📈 Laporan</a>
        <a href="{{ route('pengeluaran') }}" class="{{ request()->routeIs('pengeluaran') ? 'active' : '' }}">💸 Pengeluaran</a>
    </div>
    <div class="menu-group">
        <span class="menu-label">DATA</span>
        <a href="{{ route('riwayat') }}" class="{{ request()->routeIs('riwayat') ? 'active' : '' }}">📜 Riwayat</a>
        <a href="{{ route('pelanggan') }}" class="{{ request()->routeIs('pelanggan') ? 'active' : '' }}">👥 Pelanggan</a>
        <a href="{{ route('logout') }}" style="color:var(--danger);" onclick="return confirm('Apakah kamu yakin ingin keluar?')">🚪 Logout</a>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("mySidebar").classList.toggle("show");
}
</script>

@yield('content')
@stack('scripts')
</body>
</html>
