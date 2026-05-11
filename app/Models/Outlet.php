<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $table = 'outlet';
    protected $primaryKey = 'id_outlet';
    public $timestamps = false;

    protected $fillable = ['nama_cabang', 'alamat_outlet'];
}
