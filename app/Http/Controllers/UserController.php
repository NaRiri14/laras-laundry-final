<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Outlet; // Pastikan ini tidak merah lagi

class UserController extends Controller
{
    public function index()
{
    $users = User::with('outlet')->orderByDesc('level')->get();
    $outlets = Outlet::where('nama_cabang', 'not like', '%Owner%')->get();
    $editData = ['id'=>'','username'=>'','password'=>'','nama_cabang'=>'','alamat_outlet'=>'','level'=>'user'];

    return view('owner.users', compact('users', 'outlets', 'editData'));
}

public function edit($id)
{
    $users = User::with('outlet')->orderByDesc('level')->get();
    $outlets = Outlet::where('nama_cabang', 'not like', '%Owner%')->get();

    $user = User::with('outlet')->findOrFail($id);
    $editData = [
        'id'           => $user->id,
        'username'     => $user->username,
        'password'     => $user->password,
        'nama_cabang'  => $user->outlet->nama_cabang ?? '',
        'alamat_outlet'=> $user->outlet->alamat_outlet ?? '',
        'level'        => $user->level,
    ];

    return view('owner.users', compact('users', 'outlets', 'editData'));
}

    public function simpan(Request $request)
    {
        $namaCabang  = $request->nama_cabang;
        $alamat      = $request->alamat_cabang;

        // Cek atau buat outlet
        $outlet = Outlet::where('nama_cabang', $namaCabang)->first();
        if ($outlet) {
            $outlet->alamat_outlet = $alamat;
            $outlet->save();
        } else {
            $outlet = Outlet::create([
                'nama_cabang'   => $namaCabang,
                'alamat_outlet' => $alamat,
            ]);
        }

        if ($request->id_edit) {
            // Update user
            $user = User::findOrFail($request->id_edit);
            $user->username   = $request->username;
            $user->password   = $request->password;
            $user->id_outlet  = $outlet->id_outlet;
            $user->level      = $request->level;
            $user->save();
        } else {
            // Buat user baru
            User::create([
                'username'     => $request->username,
                'password'     => $request->password,
                'id_outlet'    => $outlet->id_outlet,
                'level'        => $request->level,
                'kode_rahasia' => 'laras123',
            ]);
        }

        return redirect()->route('users')->with('success', 'Berhasil Disimpan!');
    }

            public function hapus($id)
        {
            $user = User::findOrFail($id);

            if ($user->id == 1) {
                return redirect()->route('users')->with('error', 'Akun Owner utama tidak boleh dihapus!');
            }

            $id_outlet = $user->id_outlet;
            $user->delete();

            if ($id_outlet && $id_outlet != 1) {
                Outlet::where('id_outlet', $id_outlet)->delete();
            }

            return redirect()->route('users')->with('success', 'Berhasil Dihapus!');
        }
}
