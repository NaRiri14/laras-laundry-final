<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class CabangController extends Controller
{
    const JATAH_MINGGUAN = 500000; // Rp 500.000 per cabang per minggu

    public function index(Request $request)
    {
        $cabangSelected = $request->cabang ?? Outlet::where('nama_cabang', 'not like', '%Owner%')->first()->id_outlet;

        // Filter tanggal custom
        $tglDari   = $request->tgl_dari   ?? now()->startOfWeek()->toDateString();
        $tglSampai = $request->tgl_sampai ?? now()->toDateString();
        if ($tglSampai < $tglDari) $tglSampai = $tglDari;

        $outlets    = Outlet::where('nama_cabang', 'not like', '%Owner%')->get();
        $namaCabang = Outlet::find($cabangSelected)->nama_cabang ?? 'Cabang';

        $labels = [];
        $dataOmzet = [];
        $dataPengeluaran = [];

        $start    = Carbon::parse($tglDari);
        $end      = Carbon::parse($tglSampai);
        $diffDays = $start->diffInDays($end);

        if ($diffDays == 0) {
            for ($jam = 8; $jam <= 21; $jam++) {
                $labels[] = str_pad($jam, 2, '0', STR_PAD_LEFT) . ":00";
                $dataOmzet[] = (int) Transaksi::whereDate('tgl_masuk', $tglDari)
                    ->whereRaw('HOUR(tgl_masuk) = ?', [$jam])
                    ->where('id_outlet', $cabangSelected)->sum('total_bayar');
                $dataPengeluaran[] = (int) Pengeluaran::whereDate('tgl_pengeluaran', $tglDari)
                    ->whereRaw('HOUR(tgl_pengeluaran) = ?', [$jam])
                    ->where('id_outlet', $cabangSelected)->sum('jumlah');
            }
        } else {
            for ($i = 0; $i <= $diffDays; $i++) {
                $tgl = $start->copy()->addDays($i);
                $labels[] = $tgl->format('d/m');
                $dataOmzet[] = (int) Transaksi::whereDate('tgl_masuk', $tgl)
                    ->where('id_outlet', $cabangSelected)->sum('total_bayar');
                $dataPengeluaran[] = (int) Pengeluaran::whereDate('tgl_pengeluaran', $tgl)
                    ->where('id_outlet', $cabangSelected)->sum('jumlah');
            }
        }

        $totalIn    = array_sum($dataOmzet);
        $totalOut   = array_sum($dataPengeluaran);

        // Jatah operasional Rp 500.000 per minggu. Untuk rentang filter custom,
        // hitung berapa minggu (penuh) yang tercakup. Minimal 1 minggu jatah.
        $jumlahHariPeriode = $diffDays + 1;
        $jumlahMinggu      = max(1, ceil($jumlahHariPeriode / 7));
        $jatahPeriode      = $jumlahMinggu * self::JATAH_MINGGUAN;
        $kelebihanJatah    = max(0, $totalOut - $jatahPeriode);
        $labaBersih        = $totalIn - $kelebihanJatah;

        // Sisa jatah mingguan - SELALU mengacu pada minggu yang sedang berjalan saat ini,
        // bukan ikut rentang filter laporan. Jatah reset ke Rp 500.000 tiap minggu baru,
        // sisa minggu lalu tidak ditabung/menumpuk ke minggu berikutnya.
        $mingguIniMulai   = now()->startOfWeek();
        $mingguIniSelesai = now()->endOfWeek();

        $pengeluaranMingguIni = (int) Pengeluaran::where('id_outlet', $cabangSelected)
            ->whereBetween('tgl_pengeluaran', [$mingguIniMulai, $mingguIniSelesai])
            ->sum('jumlah');

        $jatahTotal = self::JATAH_MINGGUAN;
        $sisaJatah  = $jatahTotal - $pengeluaranMingguIni;

        // List pengeluaran
        $pengeluaranList = Pengeluaran::where('id_outlet', $cabangSelected)
            ->whereBetween('tgl_pengeluaran', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->orderByDesc('tgl_pengeluaran')->get();

        return view('owner.cabang', compact(
            'outlets', 'cabangSelected', 'namaCabang',
            'tglDari', 'tglSampai',
            'labels', 'dataOmzet', 'dataPengeluaran',
            'totalIn', 'totalOut', 'labaBersih',
            'pengeluaranList', 'jatahTotal', 'sisaJatah', 'pengeluaranMingguIni'
        ));
    }
}
