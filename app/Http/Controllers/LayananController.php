<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Layanan;

class LayananController extends Controller
{
    public function index()
    {
        $layanan = Layanan::orderByDesc('id_layanan')->get();
        return view('owner.layanan', compact('layanan'));
    }

    public function simpan(Request $request)
    {
        Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'harga'        => $request->harga,
        ]);

        return redirect()->route('layanan')->with('success', 'Layanan Berhasil Ditambah!');
    }

    public function edit(Request $request)
    {
        $layanan = Layanan::findOrFail($request->id_layanan);
        $layanan->nama_layanan = $request->nama_layanan;
        $layanan->harga        = $request->harga;
        $layanan->save();

        return redirect()->route('layanan')->with('success', 'Layanan Berhasil Diperbarui!');
    }

    public function hapus($id)
    {
        try {
            Layanan::findOrFail($id)->delete();
            return redirect()->route('layanan')->with('success', 'Layanan Berhasil Dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('layanan')->with('error', 'Layanan tidak bisa dihapus karena sudah digunakan di data transaksi!');
        }
    }
}
