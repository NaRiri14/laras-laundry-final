<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $idOutlet   = session('id_outlet');
        $filter     = $request->period ?? 'hari';
        $outlet     = Outlet::find($idOutlet);

        $labelGrafik = [];
        $dataNilai   = [];
        $tglAkhir    = now()->toDateString();

        if ($filter == 'minggu') {
            $tglAwal = Carbon::now()->startOfWeek()->toDateString();
            for ($i = 0; $i < 7; $i++) {
                $tgl = Carbon::now()->startOfWeek()->addDays($i);
                $hariIndo = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
                $labelGrafik[] = $hariIndo[$tgl->format('l')];
                $dataNilai[] = (int) Transaksi::whereDate('tgl_masuk', $tgl)
                    ->where('id_outlet', $idOutlet)->sum('total_bayar');
            }
        } elseif ($filter == 'bulan') {
            $tglAwal = now()->startOfMonth()->toDateString();
            $jumlahHari = now()->daysInMonth;
            for ($i = 1; $i <= $jumlahHari; $i++) {
                $tgl = Carbon::now()->startOfMonth()->addDays($i - 1);
                $labelGrafik[] = $i;
                $dataNilai[] = (int) Transaksi::whereDate('tgl_masuk', $tgl)
                    ->where('id_outlet', $idOutlet)->sum('total_bayar');
            }
        } else {
            $tglAwal = now()->toDateString();
            for ($jam = 8; $jam <= 21; $jam++) {
                $labelGrafik[] = str_pad($jam, 2, '0', STR_PAD_LEFT) . ":00";
                $dataNilai[] = (int) Transaksi::whereDate('tgl_masuk', today())
                    ->whereRaw('HOUR(tgl_masuk) = ?', [$jam])
                    ->where('id_outlet', $idOutlet)->sum('total_bayar');
            }
        }

        $totalPendapatan = Transaksi::whereBetween('tgl_masuk', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)->sum('total_bayar');

        $totalPengeluaran = Pengeluaran::whereBetween('tgl_pengeluaran', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)->sum('jumlah');

        $totalTransaksi = Transaksi::whereBetween('tgl_masuk', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)->count();

        $labaBersih = $totalPendapatan - $totalPengeluaran;

        $transaksiList = Transaksi::with(['pelanggan', 'layanan'])
            ->whereBetween('tgl_masuk', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)
            ->orderByDesc('tgl_masuk')->get();

        return view('kasir.laporan', compact(
            'filter', 'labelGrafik', 'dataNilai',
            'totalPendapatan', 'totalPengeluaran', 'totalTransaksi',
            'labaBersih', 'transaksiList', 'outlet'
        ));
    }
}
