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
    private function getOutlet()
    {
        return Outlet::find(session('id_outlet'));
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

        $kasBersih = $pemasukan - $pengeluaran;

        $labelHari = [];
        $dataPendapatan = [];
        $hariIndo = ['Sun'=>'Min','Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab'];

        for ($i = 6; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);
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
            'pemasukan','pengeluaran','totalOrder','kasBersih',
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

        $kasBersih = $pemasukan - $pengeluaran;

        $layanan = Layanan::orderBy('nama_layanan')->get();

        $antrian = Transaksi::with('pelanggan')
            ->where('id_outlet', $idOutlet)
            ->where('status_cucian', '!=', 'Diambil')
            ->orderByDesc('id_transaksi')
            ->limit(3)->get();

        return view('kasir.index', compact('pemasukan','pengeluaran','kasBersih','layanan','antrian'))
            ->with('outlet', $this->getOutlet());
    }

    public function simpan(Request $request)
    {
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
