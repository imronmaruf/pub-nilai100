<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('nilai_100', fn(Blueprint $table) => $table->date('tanggal_ujian')->nullable()->change());
  }
  public function down(): void
  {
    Schema::table('nilai_100', fn(Blueprint $table) => $table->date('tanggal_ujian')->nullable(false)->change());
  }
};
