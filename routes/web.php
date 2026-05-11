<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\PelangganController;

// --- HALAMAN LOGIN ---
Route::post('/login/reset', [AuthController::class, 'resetPassword'])->name('login.reset');
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// --- ROUTE KASIR (level: user) ---
Route::middleware(['auth.kasir'])->group(function () {
    Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])->name('kasir.dashboard');
    Route::get('/kasir/cek-hp', [KasirController::class, 'cekHp'])->name('kasir.cek_hp');
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir');
    Route::post('/kasir/simpan', [KasirController::class, 'simpan'])->name('kasir.simpan');

    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
    Route::get('/riwayat/update', [RiwayatController::class, 'updateStatus'])->name('riwayat.update');
    Route::post('/riwayat/edit-pelanggan', [RiwayatController::class, 'editPelanggan'])->name('riwayat.edit_pelanggan');
    Route::get('/riwayat/hapus/{id}', [RiwayatController::class, 'hapus'])->name('riwayat.hapus');

    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran');
    Route::post('/pengeluaran/simpan', [PengeluaranController::class, 'simpan'])->name('pengeluaran.simpan');
    Route::get('/pengeluaran/hapus/{id}', [PengeluaranController::class, 'hapus'])->name('pengeluaran.hapus');
    Route::get('/pengeluaran/edit/{id}', [PengeluaranController::class, 'edit'])->name('pengeluaran.edit');

    Route::get('/cetak-struk', [CetakController::class, 'struk'])->name('cetak.struk');
    Route::get('/cetak-laporan', [CetakController::class, 'laporan'])->name('cetak.laporan');
});

// --- ROUTE OWNER ---
Route::middleware(['auth.owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users/simpan', [UserController::class, 'simpan'])->name('users.simpan');
    Route::get('/users/hapus/{id}', [UserController::class, 'hapus'])->name('users.hapus');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');

    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan');
    Route::post('/layanan/simpan', [LayananController::class, 'simpan'])->name('layanan.simpan');
    Route::post('/layanan/edit', [LayananController::class, 'edit'])->name('layanan.edit');
    Route::get('/layanan/hapus/{id}', [LayananController::class, 'hapus'])->name('layanan.hapus');

    Route::get('/cabang', [CabangController::class, 'index'])->name('cabang');
    Route::get('/cetak-laporan-global', [CetakController::class, 'laporanGlobal'])->name('cetak.laporan_global');
});
