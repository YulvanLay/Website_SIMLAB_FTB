<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCatatanToBebasLaboratorium extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bebas_laboratorium', function (Blueprint $table) {
            $table->text('catatan')->nullable()->after('status_bebas');
            $table->string('no_surat')->nullable()->after('catatan');
            $table->string('nama_laboratorium')->nullable()->after('no_surat');
            $table->string('nama_periode')->nullable()->after('nama_laboratorium');
            $table->string('tanggal_pengajuan')->nullable()->after('nama_periode');
            $table->string('nama_laboran')->nullable()->after('tanggal_pengajuan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bebas_laboratorium', function (Blueprint $table) {
            $table->dropColumn(['catatan', 'no_surat', 'nama_laboratorium', 'nama_periode', 'tanggal_pengajuan', 'nama_laboran']);
        });
    }
}
