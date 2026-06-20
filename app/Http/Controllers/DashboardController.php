<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class DashboardController extends Controller
{
    const JATAH_MINGGUAN = 500000; // Rp 500.000 per cabang per minggu, reset tiap minggu

    public function index()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // Jumlah hari yang sudah berlalu di bulan ini (dari tanggal 1 s/d hari ini),
        // dipakai untuk menghitung jatah operasional secara proporsional per cabang.
        $hariBerlaluBulanIni = now()->day;

        $totalOmzet = Transaksi::whereMonth('tgl_masuk', $bulanIni)
            ->whereYear('tgl_masuk', $tahunIni)
            ->sum('total_bayar');

        $totalPengeluaran = Pengeluaran::whereMonth('tgl_pengeluaran', $bulanIni)
            ->whereYear('tgl_pengeluaran', $tahunIni)
            ->sum('jumlah');

        $cabangList    = [];
        $labaPerCabang = [];

        $outlets = Outlet::where('nama_cabang', 'not like', '%Owner%')
            ->orderBy('id_outlet')->get();

        // Jatah operasional Rp 500.000 per minggu PER CABANG. Untuk bulan berjalan,
        // hitung berapa minggu penuh yang sudah berlalu (minimal 1).
        $jumlahMingguBulanIni = max(1, ceil($hariBerlaluBulanIni / 7));
        $jatahPerCabang = $jumlahMingguBulanIni * self::JATAH_MINGGUAN;

        $totalKelebihanJatah = 0;

        foreach ($outlets as $outlet) {
            $omzet = Transaksi::where('id_outlet', $outlet->id_outlet)
                ->whereMonth('tgl_masuk', $bulanIni)
                ->whereYear('tgl_masuk', $tahunIni)
                ->sum('total_bayar');

            $pengeluaran = Pengeluaran::where('id_outlet', $outlet->id_outlet)
                ->whereMonth('tgl_pengeluaran', $bulanIni)
                ->whereYear('tgl_pengeluaran', $tahunIni)
                ->sum('jumlah');

            $kelebihanJatahCabang = max(0, $pengeluaran - $jatahPerCabang);
            $totalKelebihanJatah += $kelebihanJatahCabang;

            $cabangList[]    = $outlet->nama_cabang;
            $labaPerCabang[] = $omzet - $kelebihanJatahCabang;
        }

        $labaBersih = $totalOmzet - $totalKelebihanJatah;

        // Layanan terlaris bulan ini (global semua cabang)
        $layananTerlaris = Transaksi::with('layanan')
            ->whereMonth('tgl_masuk', $bulanIni)
            ->whereYear('tgl_masuk', $tahunIni)
            ->selectRaw('id_layanan, SUM(berat_kg) as total_kg, COUNT(*) as total_order')
            ->groupBy('id_layanan')
            ->orderByDesc('total_order')
            ->limit(5)
            ->get();

        return view('owner.dashboard', compact(
            'totalOmzet',
            'totalPengeluaran',
            'labaBersih',
            'cabangList',
            'labaPerCabang',
            'layananTerlaris'
        ));
    }
}
