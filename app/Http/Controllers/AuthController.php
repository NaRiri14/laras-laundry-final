<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('login')) {
            if (session('level') == 'owner') {
                return redirect('/dashboard');
            }
            return redirect('/kasir');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = $request->username;
        $password = $request->password;

        $user = User::where('username', $username)
                    ->where('password', $password)
                    ->first();

        if ($user) {
            session([
                'login'     => true,
                'id'        => $user->id,
                'username'  => $user->username,
                'level'     => $user->level,
                'id_outlet' => $user->id_outlet,
            ]);

            if ($user->level == 'owner') {
                return redirect('/dashboard');
            }
            return redirect('/kasir');
        }

        return redirect('/')->with('error', 'Username atau password salah!');
    }

    public function resetPassword(Request $request)
    {
        $username = $request->username_reset;
        $kode     = $request->kode_rahasia;
        $newpass  = $request->new_password;

        $user = User::where('username', $username)->where('level', 'owner')->first();

        if ($user && $kode === 'laras123') {
            $user->password = $newpass;
            $user->save();
            return redirect('/')->with('success_reset', 'Password Owner berhasil direset!');
        }

        return redirect('/')->with('error_reset', 'Username Owner atau Kode Kunci Salah!');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/')->with('success_logout', 'Anda berhasil keluar!');
    }
}
