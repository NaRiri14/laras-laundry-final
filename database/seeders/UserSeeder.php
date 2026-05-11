<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Layanan;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Outlet
        Outlet::insert([
            ['nama_cabang' => 'Laras Laundry Owner', 'alamat_outlet' => 'Kendali Laras Laundry'],
            ['nama_cabang' => 'Cabang Pusat', 'alamat_outlet' => 'Jalan. Merak V, kelayan Sel., Kota Banjarmasin'],
            ['nama_cabang' => 'Cabang A', 'alamat_outlet' => 'Dekat JL. Bunga TJ, kec. Banjarmasin Sel.'],
            ['nama_cabang' => 'Cabang B', 'alamat_outlet' => 'Jalan Raya Kompleks Jl. Raya Purna Sakti No.34'],
        ]);

        // Users
        User::insert([
            ['username' => 'Laraswati', 'password' => 'kookie', 'id_outlet' => 1, 'level' => 'owner', 'kode_rahasia' => 'laras123'],
            ['username' => 'Pusat', 'password' => '1234', 'id_outlet' => 2, 'level' => 'user', 'kode_rahasia' => 'laras123'],
            ['username' => 'cabangA', 'password' => '5678', 'id_outlet' => 3, 'level' => 'user', 'kode_rahasia' => 'laras123'],
            ['username' => 'cabangB', 'password' => '9012', 'id_outlet' => 4, 'level' => 'user', 'kode_rahasia' => 'laras123'],
        ]);

        // Layanan
        Layanan::insert([
            ['nama_layanan' => 'Cuci Kering', 'harga' => 6000],
            ['nama_layanan' => 'Cuci Setrika', 'harga' => 8000],
            ['nama_layanan' => 'Express', 'harga' => 12000],
            ['nama_layanan' => 'Cuci Kering express (1 hari)', 'harga' => 9000],
            ['nama_layanan' => 'Setrika', 'harga' => 6000],
            ['nama_layanan' => 'Super Express (3 jam)', 'harga' => 20000],
            ['nama_layanan' => 'Super Express (6 jam)', 'harga' => 17000],
        ]);
    }
}
