<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title') - Owner Laras Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0d1117;
            --card-dark: #161b22;
            --border: #30363d;
            --text-gray: #8b949e;
            --owner-accent: #ff9f43;
            --danger: #ff4d4d;
        }
        body {
            background-color: var(--bg-dark);
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }
        .sidebar {
            width: 260px; height: 100vh;
            background: var(--bg-dark);
            border-right: 1px solid var(--border);
            position: fixed;
            padding: 20px 0;
            z-index: 999;
            transition: 0.3s;
            overflow-y: auto;
        }
        .logo-area { padding: 0 25px 30px; display: flex; align-items: center; gap: 10px; }
        .logo-area h2 { font-family: 'Syne'; margin: 0; color: var(--owner-accent); font-size: 22px; }
        .menu-group { margin-bottom: 25px; }
        .menu-label { padding: 0 25px; font-size: 10px; color: #484f58; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; display: block; }
        .sidebar a { display: flex; align-items: center; padding: 12px 25px; color: var(--text-gray); text-decoration: none; font-size: 14px; transition: 0.3s; gap: 12px; }
        .sidebar a:hover { color: white; background: rgba(255,255,255,0.05); }
        .sidebar a.active { background: rgba(255,159,67,0.1) !important; color: var(--owner-accent) !important; border-right: 3px solid var(--owner-accent); font-weight: 600; }
        .mobile-nav {
            display: none;
            background: var(--card-dark);
            padding: 15px 20px;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 1001;
        }
        .hamburger { cursor: pointer; display: flex; flex-direction: column; gap: 5px; }
        .hamburger span { display: block; width: 25px; height: 3px; background-color: var(--owner-accent); border-radius: 2px; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 998; }
        .sidebar-overlay.active { display: block; }
        @media (max-width: 768px) {
            .mobile-nav { display: flex; }
            .sidebar { left: -260px; }
            .sidebar.active { left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="mobile-nav">
    <div style="font-family:'Syne'; font-weight:bold; color:var(--owner-accent);">LARAS LAUNDRY</div>
    <div class="hamburger" onclick="toggleSidebar()">
        <span></span><span></span><span></span>
    </div>
</div>

<div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="logo-area">
        <span>👑</span>
        <h2>Laras Laundry</h2>
    </div>

    <div class="menu-group">
        <span class="menu-label">ANALITIK</span>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            📊 Dashboard Global
        </a>
        <a href="{{ route('cabang') }}" class="{{ request()->routeIs('cabang') ? 'active' : '' }}">
            📍 Laporan Analitik Cabang
        </a>
    </div>

    <div class="menu-group">
        <span class="menu-label">PENGATURAN BISNIS</span>
        <a href="{{ route('users') }}" class="{{ request()->routeIs('users') ? 'active' : '' }}">
            👥 Manajemen User
        </a>
        <a href="{{ route('layanan') }}" class="{{ request()->routeIs('layanan') ? 'active' : '' }}">
            🛠️ Jenis Layanan & Harga
        </a>
    </div>

    <div class="menu-group">
        <span class="menu-label">SISTEM</span>
        <a href="{{ route('logout') }}" style="color:var(--danger);" onclick="return confirm('Apakah kamu yakin ingin keluar?')">
            🚪 Logout
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}
</script>

@yield('content')
@stack('scripts')
</body>
</html>
