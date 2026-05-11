<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // Total semua cabang bulan ini
        $totalOmzet = Transaksi::whereMonth('tgl_masuk', $bulanIni)
            ->whereYear('tgl_masuk', $tahunIni)
            ->sum('total_bayar');

        $totalPengeluaran = Pengeluaran::whereMonth('tgl_pengeluaran', $bulanIni)
            ->whereYear('tgl_pengeluaran', $tahunIni)
            ->sum('jumlah');

        $labaBersih = $totalOmzet - $totalPengeluaran;

        // Data per cabang bulan ini
        $cabangList    = [];
        $labaPerCabang = [];

        $outlets = Outlet::where('nama_cabang', 'not like', '%Owner%')
            ->orderBy('id_outlet')->get();

        foreach ($outlets as $outlet) {
            $omzet = Transaksi::where('id_outlet', $outlet->id_outlet)
                ->whereMonth('tgl_masuk', $bulanIni)
                ->whereYear('tgl_masuk', $tahunIni)
                ->sum('total_bayar');

            $pengeluaran = Pengeluaran::where('id_outlet', $outlet->id_outlet)
                ->whereMonth('tgl_pengeluaran', $bulanIni)
                ->whereYear('tgl_pengeluaran', $tahunIni)
                ->sum('jumlah');

            $cabangList[]    = $outlet->nama_cabang;
            $labaPerCabang[] = $omzet - $pengeluaran;
        }

        return view('owner.dashboard', compact(
            'totalOmzet',
            'totalPengeluaran',
            'labaBersih',
            'cabangList',
            'labaPerCabang'
        ));
    }
}
