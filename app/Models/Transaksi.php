<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;

    protected $fillable = [
        'id_outlet','id_pelanggan','id_user','tgl_masuk',
        'id_layanan','berat_kg','total_bayar','status_cucian','catatan'
    ];

    public function pelanggan() {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
    public function layanan() {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }
    public function outlet() {
        return $this->belongsTo(Outlet::class, 'id_outlet', 'id_outlet');
    }
}
