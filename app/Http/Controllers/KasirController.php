<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengeluaran;
use App\Models\Layanan;
use App\Models\Outlet;
use Carbon\Carbon;

class KasirController extends Controller
{
    const MIN_BERAT_KG = 1; // Minimal order 1 kg
    const JATAH_MINGGUAN = 500000; // Rp 500.000 per cabang per minggu, reset tiap minggu

    private function getOutlet()
    {
        return Outlet::find(session('id_outlet'));
    }

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

    public function dashboard()
    {
        $idOutlet = session('id_outlet');
        $today = Carbon::today();

        $pemasukan = Transaksi::whereDate('tgl_masuk', $today)
            ->where('id_outlet', $idOutlet)->sum('total_bayar');

        $pengeluaran = Pengeluaran::whereDate('tgl_pengeluaran', $today)
            ->where('id_outlet', $idOutlet)->sum('jumlah');

        $totalOrder = Transaksi::whereDate('tgl_masuk', $today)
            ->where('id_outlet', $idOutlet)->count();

        ['jatahTotal' => $jatahTotal, 'sisaJatah' => $sisaJatah, 'pengeluaranMingguIni' => $pengeluaranMingguIni]
            = $this->hitungJatahMingguan($idOutlet);

        // Pengeluaran diambil dari jatah mingguan dulu. Laba bersih cuma berkurang
        // kalau pengeluaran minggu ini sudah melebihi jatah (kelebihannya baru "makan" pendapatan).
        $kelebihanJatah = max(0, $pengeluaranMingguIni - $jatahTotal);
        $labaBersih = $pemasukan - $kelebihanJatah;

        // Grafik: Senin s/d Minggu minggu ini (bukan 7 hari terakhir)
        $labelHari = [];
        $dataPendapatan = [];
        $hariIndo = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];
        $seninMingguIni = Carbon::now()->startOfWeek(Carbon::MONDAY); // Senin

        for ($i = 0; $i <= 6; $i++) {
            $tgl = $seninMingguIni->copy()->addDays($i);
            $labelHari[] = $hariIndo[$tgl->format('D')];
            $dataPendapatan[] = Transaksi::whereDate('tgl_masuk', $tgl)
                ->where('id_outlet', $idOutlet)->sum('total_bayar');
        }

        $layananTerlaris = Transaksi::with('layanan')
            ->where('id_outlet', $idOutlet)
            ->selectRaw('id_layanan, COUNT(*) as jml')
            ->groupBy('id_layanan')
            ->orderByDesc('jml')
            ->limit(4)->get();

        $transaksiTerbaru = Transaksi::with('pelanggan')
            ->where('id_outlet', $idOutlet)
            ->orderByDesc('id_transaksi')
            ->limit(4)->get();

        return view('kasir.dashboard', compact(
            'pemasukan','pengeluaran','totalOrder','labaBersih',
            'jatahTotal','sisaJatah','pengeluaranMingguIni','kelebihanJatah',
            'labelHari','dataPendapatan','layananTerlaris','transaksiTerbaru'
        ))->with('outlet', $this->getOutlet());
    }

    public function index()
    {
        $idOutlet = session('id_outlet');
        $today = Carbon::today();

        $pemasukan = Transaksi::whereDate('tgl_masuk', $today)
            ->where('id_outlet', $idOutlet)->sum('total_bayar');

        $pengeluaran = Pengeluaran::whereDate('tgl_pengeluaran', $today)
            ->where('id_outlet', $idOutlet)->sum('jumlah');

        // Laba bersih: pendapatan hanya berkurang jika pengeluaran minggu ini melebihi jatah
        ['jatahTotal' => $jatahTotal, 'sisaJatah' => $sisaJatah, 'pengeluaranMingguIni' => $pengeluaranMingguIni]
            = $this->hitungJatahMingguan($idOutlet);
        $kelebihanJatah = max(0, $pengeluaranMingguIni - $jatahTotal);
        $kasBersih = $pemasukan - $kelebihanJatah;

        $layanan = Layanan::orderBy('nama_layanan')->get();

        $antrian = Transaksi::with('pelanggan')
            ->where('id_outlet', $idOutlet)
            ->where('status_cucian', '!=', 'Diambil')
            ->orderByDesc('id_transaksi')
            ->limit(3)->get();

        return view('kasir.index', compact('pemasukan','pengeluaran','kasBersih','layanan','antrian'))
            ->with('outlet', $this->getOutlet())
            ->with('minBerat', self::MIN_BERAT_KG);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'berat_kg' => 'required|numeric|min:' . self::MIN_BERAT_KG,
        ], [
            'berat_kg.min' => 'Minimal order adalah ' . self::MIN_BERAT_KG . ' kg.',
        ]);

        $idOutlet  = session('id_outlet');
        $idUser    = session('id');
        $nama      = $request->nama_pelanggan;
        $hp        = $request->no_hp;
        $idLayanan = $request->id_layanan;
        $berat     = $request->berat_kg;
        $total     = $request->total_bayar;
        $bayar     = $request->bayar;
        $catatan   = $request->catatan;

        $pelanggan = \App\Models\Pelanggan::firstOrCreate(
            ['no_hp' => $hp],
            ['nama_pelanggan' => $nama]
        );

        $transaksi = Transaksi::create([
            'id_outlet'     => $idOutlet,
            'id_pelanggan'  => $pelanggan->id_pelanggan,
            'id_user'       => $idUser,
            'tgl_masuk'     => now(),
            'id_layanan'    => $idLayanan,
            'berat_kg'      => $berat,
            'total_bayar'   => $total,
            'status_cucian' => 'Proses',
            'catatan'       => $catatan,
        ]);

        return redirect()->route('kasir')
            ->with('success', 'Transaksi berhasil disimpan!')
            ->with('print_id', $transaksi->id_transaksi)
            ->with('print_bayar', $bayar);
    }

    public function cekHp(Request $request)
    {
        $pelanggan = \App\Models\Pelanggan::where('no_hp', $request->hp)->first();
        return response()->json($pelanggan);
    }
}
