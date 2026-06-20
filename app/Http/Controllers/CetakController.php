<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class CetakController extends Controller
{
    const JATAH_MINGGUAN = 500000;

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

    // Hitung riwayat jatah per minggu dalam periode (untuk 1 cabang)
    private function hitungRiwayatJatah($tglDari, $tglSampai, $idOutlet = null)
    {
        $start = Carbon::parse($tglDari)->startOfWeek(Carbon::MONDAY);
        $end   = Carbon::parse($tglSampai)->endOfWeek(Carbon::SUNDAY);

        $riwayat = [];
        $current = $start->copy();

        while ($current <= Carbon::parse($tglSampai)) {
            $mingguMulai  = $current->copy()->startOfWeek(Carbon::MONDAY);
            $mingguSelesai = $current->copy()->endOfWeek(Carbon::SUNDAY);

            // Batasi ke rentang filter
            $dari    = $mingguMulai->lt(Carbon::parse($tglDari)) ? Carbon::parse($tglDari) : $mingguMulai;
            $sampai  = $mingguSelesai->gt(Carbon::parse($tglSampai)) ? Carbon::parse($tglSampai) : $mingguSelesai;

            $query = Pengeluaran::whereBetween('tgl_pengeluaran', [
                $dari->format('Y-m-d') . ' 00:00:00',
                $sampai->format('Y-m-d') . ' 23:59:59'
            ]);
            if ($idOutlet) $query->where('id_outlet', $idOutlet);

            $pengeluaranMinggu = (int) $query->sum('jumlah');
            $jatah = self::JATAH_MINGGUAN;
            $sisa  = $jatah - $pengeluaranMinggu;

            $riwayat[] = [
                'label'       => $mingguMulai->format('d/m') . ' - ' . $mingguSelesai->format('d/m/Y'),
                'pengeluaran' => $pengeluaranMinggu,
                'jatah'       => $jatah,
                'sisa'        => $sisa,
            ];

            $current->addWeek();
        }

        return $riwayat;
    }

    public function laporan(Request $request)
    {
        $idOutlet  = $request->cabang ?? 1;
        $tglDari   = $request->tgl_dari   ?? now()->toDateString();
        $tglSampai = $request->tgl_sampai ?? now()->toDateString();
        if ($tglSampai < $tglDari) $tglSampai = $tglDari;

        $outlet = Outlet::findOrFail($idOutlet);

        $judulPeriode = "Laporan " . Carbon::parse($tglDari)->format('d M Y');
        if ($tglDari != $tglSampai) {
            $judulPeriode .= " s/d " . Carbon::parse($tglSampai)->format('d M Y');
        }

        $transaksiList = Transaksi::with(['pelanggan', 'layanan'])
            ->where('id_outlet', $idOutlet)
            ->whereBetween('tgl_masuk', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->orderByDesc('id_transaksi')->get();

        $riwayatLayanan = $transaksiList
            ->groupBy(function ($trx) { return $trx->layanan->nama_layanan ?? 'Lainnya'; })
            ->map(function ($group) { return $group->count(); })
            ->sortDesc();

        $pengeluaranList = Pengeluaran::where('id_outlet', $idOutlet)
            ->whereBetween('tgl_pengeluaran', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->orderBy('tgl_pengeluaran')->get();

        $totalIn          = $transaksiList->sum('total_bayar');
        $totalPengeluaran = $pengeluaranList->sum('jumlah');

        $jumlahHari   = Carbon::parse($tglDari)->diffInDays(Carbon::parse($tglSampai)) + 1;
        $jumlahMinggu = max(1, ceil($jumlahHari / 7));
        $jatahPeriode = $jumlahMinggu * self::JATAH_MINGGUAN;
        $labaBersih   = $totalIn - max(0, $totalPengeluaran - $jatahPeriode);

        $riwayatJatah = $this->hitungRiwayatJatah($tglDari, $tglSampai, $idOutlet);

        return view('cetak.laporan', compact(
            'outlet', 'judulPeriode', 'transaksiList', 'riwayatLayanan',
            'pengeluaranList', 'totalIn', 'totalPengeluaran', 'labaBersih',
            'tglDari', 'tglSampai', 'riwayatJatah'
        ));
    }

    public function laporanGlobal(Request $request)
    {
        $tglDari   = $request->tgl_dari   ?? now()->startOfMonth()->toDateString();
        $tglSampai = $request->tgl_sampai ?? now()->toDateString();
        if ($tglSampai < $tglDari) $tglSampai = $tglDari;

        $judulPeriode = "Laporan " . Carbon::parse($tglDari)->format('d M Y');
        if ($tglDari != $tglSampai) {
            $judulPeriode .= " s/d " . Carbon::parse($tglSampai)->format('d M Y');
        }

        $transaksiList = Transaksi::with(['pelanggan', 'layanan', 'outlet'])
            ->whereBetween('tgl_masuk', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->orderBy('tgl_masuk')->get();

        $riwayatLayanan = $transaksiList
            ->groupBy(function ($trx) { return $trx->layanan->nama_layanan ?? 'Lainnya'; })
            ->map(function ($group) { return $group->count(); })
            ->sortDesc();

        $pengeluaranList = Pengeluaran::with('outlet')
            ->whereBetween('tgl_pengeluaran', [$tglDari . ' 00:00:00', $tglSampai . ' 23:59:59'])
            ->orderBy('tgl_pengeluaran')->get();

        $totalIn          = $transaksiList->sum('total_bayar');
        $totalPengeluaran = $pengeluaranList->sum('jumlah');

        // Laba bersih global: kelebihan jatah dihitung per cabang
        $outlets = Outlet::where('nama_cabang', 'not like', '%Owner%')->get();
        $jumlahHari   = Carbon::parse($tglDari)->diffInDays(Carbon::parse($tglSampai)) + 1;
        $jumlahMinggu = max(1, ceil($jumlahHari / 7));
        $jatahPerCabang = $jumlahMinggu * self::JATAH_MINGGUAN;

        $totalKelebihanJatah = 0;
        foreach ($outlets as $outlet) {
            $pengeluaranCabang = $pengeluaranList->where('id_outlet', $outlet->id_outlet)->sum('jumlah');
            $totalKelebihanJatah += max(0, $pengeluaranCabang - $jatahPerCabang);
        }
        $labaBersih = $totalIn - $totalKelebihanJatah;

        // Riwayat jatah per minggu per cabang
        $riwayatJatahPerCabang = [];
        foreach ($outlets as $outlet) {
            $riwayatJatahPerCabang[$outlet->nama_cabang] = $this->hitungRiwayatJatah($tglDari, $tglSampai, $outlet->id_outlet);
        }

        return view('cetak.laporan_global', compact(
            'judulPeriode', 'transaksiList', 'riwayatLayanan',
            'pengeluaranList', 'totalIn', 'totalPengeluaran', 'labaBersih',
            'riwayatJatahPerCabang'
        ));
    }
}
