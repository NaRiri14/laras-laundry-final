<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class CabangController extends Controller
{
    public function index(Request $request)
    {
        $cabangSelected = $request->cabang ?? Outlet::where('nama_cabang', 'not like', '%Owner%')->first()->id_outlet;
        $filterWaktu    = $request->waktu ?? 'hari';

        $outlets  = Outlet::where('nama_cabang', 'not like', '%Owner%')->get();
        $namaCabang = Outlet::find($cabangSelected)->nama_cabang ?? 'Cabang';

        $labels = [];
        $dataOmzet = [];
        $dataPengeluaran = [];

        if ($filterWaktu == 'hari') {
            for ($jam = 8; $jam <= 21; $jam++) {
                $labels[] = str_pad($jam, 2, '0', STR_PAD_LEFT) . ":00";
                $dataOmzet[] = (int) Transaksi::whereDate('tgl_masuk', today())
                    ->whereRaw('HOUR(tgl_masuk) = ?', [$jam])
                    ->where('id_outlet', $cabangSelected)
                    ->sum('total_bayar');
                // Pengeluaran juga per jam, bukan ditumpuk di jam 8
                $dataPengeluaran[] = (int) Pengeluaran::whereDate('tgl_pengeluaran', today())
                    ->whereRaw('HOUR(tgl_pengeluaran) = ?', [$jam])
                    ->where('id_outlet', $cabangSelected)
                    ->sum('jumlah');
            }
        } elseif ($filterWaktu == 'minggu') {
            $hariIndo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
            for ($i = 6; $i >= 0; $i--) {
                $tgl = Carbon::today()->subDays($i);
                $labels[] = $hariIndo[$tgl->format('l')];
                $dataOmzet[] = (int) Transaksi::whereDate('tgl_masuk', $tgl)
                    ->where('id_outlet', $cabangSelected)->sum('total_bayar');
                $dataPengeluaran[] = (int) Pengeluaran::whereDate('tgl_pengeluaran', $tgl)
                    ->where('id_outlet', $cabangSelected)->sum('jumlah');
            }
        } else {
            for ($w = 1; $w <= 4; $w++) {
                $labels[] = "Mgg $w";
                $startDay = ($w - 1) * 7 + 1;
                $endDay   = ($w == 4) ? 31 : $w * 7;
                $dataOmzet[] = (int) Transaksi::whereMonth('tgl_masuk', now()->month)
                    ->whereYear('tgl_masuk', now()->year)
                    ->whereRaw('DAY(tgl_masuk) BETWEEN ? AND ?', [$startDay, $endDay])
                    ->where('id_outlet', $cabangSelected)->sum('total_bayar');
                $dataPengeluaran[] = (int) Pengeluaran::whereMonth('tgl_pengeluaran', now()->month)
                    ->whereYear('tgl_pengeluaran', now()->year)
                    ->whereRaw('DAY(tgl_pengeluaran) BETWEEN ? AND ?', [$startDay, $endDay])
                    ->where('id_outlet', $cabangSelected)->sum('jumlah');
            }
        }

        $totalIn    = array_sum($dataOmzet);
        $totalOut   = array_sum($dataPengeluaran);
        $labaBersih = $totalIn - $totalOut;

        // List pengeluaran
        $query = Pengeluaran::where('id_outlet', $cabangSelected);
        if ($filterWaktu == 'hari') {
            $query->whereDate('tgl_pengeluaran', today());
        } elseif ($filterWaktu == 'minggu') {
            $query->where('tgl_pengeluaran', '>=', Carbon::today()->subDays(6));
        } else {
            $query->whereMonth('tgl_pengeluaran', now()->month)
                  ->whereYear('tgl_pengeluaran', now()->year);
        }
        $pengeluaranList = $query->orderByDesc('tgl_pengeluaran')->get();

        return view('owner.cabang', compact(
            'outlets', 'cabangSelected', 'filterWaktu', 'namaCabang',
            'labels', 'dataOmzet', 'dataPengeluaran',
            'totalIn', 'totalOut', 'labaBersih', 'pengeluaranList'
        ));
    }
}
