<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Outlet;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    public function index()
    {
        $idOutlet    = session('id_outlet');
        $outlet      = Outlet::find($idOutlet);
        $pengeluaran = Pengeluaran::where('id_outlet', $idOutlet)
            ->orderByDesc('tgl_pengeluaran')->get();

        return view('kasir.pengeluaran', compact('pengeluaran', 'outlet'));
    }

    public function simpan(Request $request)
    {
        $idOutlet = session('id_outlet');
        $idUser   = session('id');

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $file     = $request->file('foto_bukti');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('img_bukti'), $fileName);
            $fotoPath = $fileName;
        }

        Pengeluaran::create([
            'id_outlet'       => $idOutlet,
            'tgl_pengeluaran' => now(),
            'keterangan'      => $request->keterangan,
            'jumlah'          => $request->jumlah,
            'id_user'         => $idUser,
            'foto_bukti'      => $fotoPath,
        ]);

        return redirect()->route('pengeluaran')->with('success', 'Pengeluaran berhasil disimpan!');
    }

    public function edit($id)
    {
        $idOutlet    = session('id_outlet');
        $outlet      = Outlet::find($idOutlet);
        $pengeluaran = Pengeluaran::where('id_outlet', $idOutlet)
            ->orderByDesc('tgl_pengeluaran')->get();
        $edit        = Pengeluaran::findOrFail($id);

        return view('kasir.pengeluaran', compact('pengeluaran', 'outlet', 'edit'));
    }

    public function hapus($id)
    {
        Pengeluaran::findOrFail($id)->delete();
        return redirect()->route('pengeluaran')->with('success', 'Pengeluaran berhasil dihapus!');
    }
}
