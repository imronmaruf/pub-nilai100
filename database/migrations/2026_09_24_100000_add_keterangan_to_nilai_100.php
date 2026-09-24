<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('nilai_100', function (Blueprint $table) {
      $table->date('tanggal_ujian')->nullable()->change();
      $table->date('tanggal_validasi_pt')->nullable()->change();
    });
    if (!Schema::hasColumn('nilai_100', 'keterangan')) Schema::table('nilai_100', fn(Blueprint $table) => $table->text('keterangan')->nullable()->after('status_testimoni'));
  }
  public function down(): void
  {
    if (Schema::hasColumn('nilai_100', 'keterangan')) Schema::table('nilai_100', fn(Blueprint $table) => $table->dropColumn('keterangan'));
    Schema::table('nilai_100', function (Blueprint $table) {
      $table->date('tanggal_ujian')->nullable(false)->change();
      $table->date('tanggal_validasi_pt')->nullable(false)->change();
    });
  }
};
