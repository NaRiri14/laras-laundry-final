<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Outlet;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $idOutlet = session('id_outlet');
        $keyword  = $request->cari ?? '';
        $outlet   = Outlet::find($idOutlet);

        // Ambil semua transaksi aktif, group by pelanggan + status
        $pelanggan = Transaksi::with(['pelanggan', 'layanan'])
            ->where('id_outlet', $idOutlet)
            ->where('status_cucian', '!=', 'Diambil')
            ->whereHas('pelanggan', function($q) use ($keyword) {
                $q->where('nama_pelanggan', 'like', "%$keyword%");
            })
            ->orderByDesc('id_transaksi')
            ->get()
            ->groupBy('id_pelanggan')
            ->map(function($group) {
                $first = $group->first();
                return (object)[
                    'pelanggan'      => $first->pelanggan,
                    'status_cucian'  => $first->status_cucian,
                    'total_rp'       => $group->sum('total_bayar'),
                    'semua_layanan'  => $group->map(fn($t) => $t->layanan->nama_layanan ?? '')->filter()->implode(' + '),
                ];
            })->values();

        return view('kasir.pelanggan', compact('pelanggan', 'keyword', 'outlet'));
    }
}
