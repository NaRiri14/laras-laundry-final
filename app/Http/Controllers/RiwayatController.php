<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pelanggan;
use App\Models\Outlet;
use Carbon\Carbon;

class RiwayatController extends Controller
{
    public function index(Request $request)
        {
            $idOutlet     = session('id_outlet');
            $filterStatus = $request->filter ?? 'Semua';
            $bulanIni     = now()->month;
            $tahunIni     = now()->year;
            $outlet       = Outlet::find($idOutlet);

            $query = Transaksi::with(['pelanggan', 'layanan'])
                ->where('id_outlet', $idOutlet)
                ->where(function($q) use ($bulanIni, $tahunIni) {
                    // Transaksi bulan ini tampil semua
                    $q->where(function($q2) use ($bulanIni, $tahunIni) {
                        $q2->whereMonth('tgl_masuk', $bulanIni)
                        ->whereYear('tgl_masuk', $tahunIni);
                    })
                    // ATAU belum diambil dari bulan manapun
                    ->orWhere('status_cucian', '!=', 'Diambil');
                })
                ->orderByDesc('id_transaksi');

            if ($filterStatus != 'Semua') {
                $query->where('status_cucian', $filterStatus);
            }

            $transaksi = $query->get();

            return view('kasir.riwayat', compact('transaksi', 'filterStatus', 'outlet'));
}

    public function updateStatus(Request $request)
    {
        $id         = $request->update_id;
        $statusBaru = $request->status_baru;
        $filter     = $request->filter ?? 'Semua';

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_cucian = $statusBaru;

        // Simpan waktu pengambilan di updated_at saat status jadi Diambil
        if ($statusBaru == 'Diambil') {
            $transaksi->updated_at = now();
        }

        $transaksi->save();

        return redirect()->route('riwayat', ['filter' => $filter])
            ->with('success', $statusBaru == 'Diambil' ? 'Pakaian berhasil diambil!' : 'Status berhasil diupdate!');
    }

    public function editPelanggan(Request $request)
    {
        $pelanggan = Pelanggan::findOrFail($request->id_pelanggan);
        $pelanggan->nama_pelanggan = $request->nama_baru;
        $pelanggan->no_hp          = $request->hp_baru;
        $pelanggan->save();

        return redirect()->route('riwayat')->with('success', 'Data Pelanggan Berhasil Diubah!');
    }

    public function hapus($id)
    {
        Transaksi::findOrFail($id)->delete();
        return redirect()->route('riwayat')->with('success', 'Data berhasil dihapus!');
    }
}
