<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->unsignedBigInteger('id_outlet');
            $table->unsignedBigInteger('id_pelanggan');
            $table->dateTime('tgl_masuk');
            $table->unsignedBigInteger('id_layanan');
            $table->float('berat_kg');
            $table->integer('total_bayar');
            $table->enum('status_cucian', ['Proses', 'Selesai', 'Diambil'])->default('Proses');
            $table->text('keterangan')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamps();

            $table->foreign('id_outlet')->references('id_outlet')->on('outlet');
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
            $table->foreign('id_layanan')->references('id_layanan')->on('layanan');
        });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
};
