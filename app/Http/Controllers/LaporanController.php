<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class LaporanController extends Controller
{
    const JATAH_MINGGUAN = 500000; // Rp 500.000 per cabang per minggu, reset tiap minggu

    private function hitungJatahMingguan($idOutlet)
    {
        $mingguIniMulai   = now()->startOfWeek();
        $mingguIniSelesai = now()->endOfWeek();

        $pengeluaranMingguIni = (int) Pengeluaran::where('id_outlet', $idOutlet)
            ->whereBetween('tgl_pengeluaran', [$mingguIniMulai, $mingguIniSelesai])
            ->sum('jumlah');

        $jatahTotal = self::JATAH_MINGGUAN;
        $sisaJatah  = $jatahTotal - $pengeluaranMingguIni;

        return compact('jatahTotal', 'sisaJatah', 'pengeluaranMingguIni');
    }

    public function index(Request $request)
    {
        $idOutlet = session('id_outlet');
        $outlet   = Outlet::find($idOutlet);

        // Filter tanggal custom
        $tglDari  = $request->tgl_dari ?? now()->toDateString();
        $tglSampai = $request->tgl_sampai ?? now()->toDateString();

        // Pastikan tglSampai tidak lebih kecil dari tglDari
        if ($tglSampai < $tglDari) $tglSampai = $tglDari;

        $labelGrafik = [];
        $dataNilai   = [];

        // Grafik per hari dalam range
        $start = Carbon::parse($tglDari);
        $end   = Carbon::parse($tglSampai);
        $diffDays = $start->diffInDays($end);

        if ($diffDays == 0) {
            // Tampilkan per jam jika 1 hari
            for ($jam = 8; $jam <= 21; $jam++) {
                $labelGrafik[] = str_pad($jam, 2, '0', STR_PAD_LEFT) . ":00";
                $dataNilai[] = (int) Transaksi::whereDate('tgl_masuk', $tglDari)
                    ->whereRaw('HOUR(tgl_masuk) = ?', [$jam])
                    ->where('id_outlet', $idOutlet)->sum('total_bayar');
            }
        } else {
            // Tampilkan per hari
            for ($i = 0; $i <= $diffDays; $i++) {
                $tgl = $start->copy()->addDays($i);
                $labelGrafik[] = $tgl->format('d/m');
                $dataNilai[] = (int) Transaksi::whereDate('tgl_masuk', $tgl->toDateString())
                    ->where('id_outlet', $idOutlet)->sum('total_bayar');
            }
        }

        $totalPendapatan = Transaksi::whereBetween('tgl_masuk', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)->sum('total_bayar');

        $totalPengeluaran = Pengeluaran::whereBetween('tgl_pengeluaran', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)->sum('jumlah');

        $totalTransaksi = Transaksi::whereBetween('tgl_masuk', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)->count();

        ['jatahTotal' => $jatahTotal, 'sisaJatah' => $sisaJatah, 'pengeluaranMingguIni' => $pengeluaranMingguIni]
            = $this->hitungJatahMingguan($idOutlet);

        // Pengeluaran diambil dari jatah mingguan dulu. Laba bersih cuma berkurang
        // kalau pengeluaran minggu ini sudah melebihi jatah (kelebihannya baru "makan" pendapatan).
        $kelebihanJatah = max(0, $pengeluaranMingguIni - $jatahTotal);
        $labaBersih = $totalPendapatan - $kelebihanJatah;

        $transaksiList = Transaksi::with(['pelanggan', 'layanan'])
            ->whereBetween('tgl_masuk', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->where('id_outlet', $idOutlet)
            ->orderByDesc('tgl_masuk')->get();

        return view('kasir.laporan', compact(
            'tglDari', 'tglSampai', 'labelGrafik', 'dataNilai',
            'totalPendapatan', 'totalPengeluaran', 'totalTransaksi',
            'labaBersih', 'transaksiList', 'outlet',
            'jatahTotal', 'sisaJatah', 'pengeluaranMingguIni', 'kelebihanJatah'
        ));
    }
}
