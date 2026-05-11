<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class CetakController extends Controller
{
    public function struk(Request $request)
    {
        $id    = $request->id;
        $bayar = $request->bayar ?? 0;

        $transaksi = Transaksi::with(['pelanggan', 'layanan', 'outlet'])
                        ->findOrFail($id);

        $bayar   = $bayar != 0 ? (int)$bayar : (int)$transaksi->total_bayar;
        $total   = (int)$transaksi->total_bayar;
        $kembali = $bayar - $total;

        return view('cetak.struk', compact('transaksi', 'bayar', 'total', 'kembali'));
    }

    public function laporan(Request $request)
    {
        $idOutlet = $request->cabang ?? 1;
        $waktu    = $request->waktu ?? 'hari';
        $outlet   = Outlet::findOrFail($idOutlet);

        if ($waktu == 'hari') {
            $judulPeriode = "Laporan Harian (" . Carbon::now()->format('d M Y') . ")";
            $transaksiList = Transaksi::with(['pelanggan', 'layanan'])
                ->where('id_outlet', $idOutlet)
                ->whereDate('tgl_masuk', Carbon::today())
                ->orderByDesc('id_transaksi')->get();
            $pengeluaranList = Pengeluaran::where('id_outlet', $idOutlet)
                ->whereDate('tgl_pengeluaran', Carbon::today())->get();
        } elseif ($waktu == 'minggu') {
            $judulPeriode = "Laporan Mingguan (7 Hari Terakhir)";
            $transaksiList = Transaksi::with(['pelanggan', 'layanan'])
                ->where('id_outlet', $idOutlet)
                ->whereDate('tgl_masuk', '>=', Carbon::now()->subDays(7))
                ->orderByDesc('id_transaksi')->get();
            $pengeluaranList = Pengeluaran::where('id_outlet', $idOutlet)
                ->whereDate('tgl_pengeluaran', '>=', Carbon::now()->subDays(7))->get();
        } else {
            $judulPeriode = "Laporan Bulanan (Bulan " . Carbon::now()->format('F Y') . ")";
            $transaksiList = Transaksi::with(['pelanggan', 'layanan'])
                ->where('id_outlet', $idOutlet)
                ->whereMonth('tgl_masuk', Carbon::now()->month)
                ->whereYear('tgl_masuk', Carbon::now()->year)
                ->orderByDesc('id_transaksi')->get();
            $pengeluaranList = Pengeluaran::where('id_outlet', $idOutlet)
                ->whereMonth('tgl_pengeluaran', Carbon::now()->month)
                ->whereYear('tgl_pengeluaran', Carbon::now()->year)->get();
        }

        $totalIn  = $transaksiList->sum('total_bayar');
        $totalOut = $pengeluaranList->sum('jumlah');
        $labaBersih = $totalIn - $totalOut;

        return view('cetak.laporan', compact(
            'outlet', 'judulPeriode', 'transaksiList',
            'pengeluaranList', 'totalIn', 'totalOut', 'labaBersih'
        ));
    }

    public function laporanGlobal(Request $request)
    {
        $waktu = $request->waktu ?? 'bulan';

        if ($waktu == 'hari') {
            $judulPeriode = "Laporan Harian Gabungan (" . Carbon::now()->format('d M Y') . ")";
            $transaksiList = Transaksi::with(['pelanggan', 'layanan', 'outlet'])
                ->whereDate('tgl_masuk', Carbon::today())
                ->orderBy('tgl_masuk')->get();
            $pengeluaranList = Pengeluaran::with('outlet')
                ->whereDate('tgl_pengeluaran', Carbon::today())->get();
        } elseif ($waktu == 'minggu') {
            $judulPeriode = "Laporan Mingguan Gabungan (7 Hari Terakhir)";
            $transaksiList = Transaksi::with(['pelanggan', 'layanan', 'outlet'])
                ->whereDate('tgl_masuk', '>=', Carbon::now()->subDays(7))
                ->orderBy('tgl_masuk')->get();
            $pengeluaranList = Pengeluaran::with('outlet')
                ->whereDate('tgl_pengeluaran', '>=', Carbon::now()->subDays(7))->get();
        } else {
            $judulPeriode = "Laporan Bulanan Gabungan (Periode: " . Carbon::now()->format('F Y') . ")";
            $transaksiList = Transaksi::with(['pelanggan', 'layanan', 'outlet'])
                ->whereMonth('tgl_masuk', Carbon::now()->month)
                ->whereYear('tgl_masuk', Carbon::now()->year)
                ->orderBy('tgl_masuk')->get();
            $pengeluaranList = Pengeluaran::with('outlet')
                ->whereMonth('tgl_pengeluaran', Carbon::now()->month)
                ->whereYear('tgl_pengeluaran', Carbon::now()->year)->get();
        }

        $totalIn    = $transaksiList->sum('total_bayar');
        $totalOut   = $pengeluaranList->sum('jumlah');
        $labaBersih = $totalIn - $totalOut;

        return view('cetak.laporan_global', compact(
            'judulPeriode', 'transaksiList', 'pengeluaranList',
            'totalIn', 'totalOut', 'labaBersih'
        ));
    }
}
