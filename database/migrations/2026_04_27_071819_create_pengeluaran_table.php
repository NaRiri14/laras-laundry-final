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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id('id_pengeluaran');
            $table->unsignedBigInteger('id_outlet');
            $table->dateTime('tgl_pengeluaran');
            $table->string('keterangan', 255);
            $table->integer('jumlah');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('foto_bukti', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_outlet')->references('id_outlet')->on('outlet');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaran');
    }
};
