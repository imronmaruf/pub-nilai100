<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasColumn('nilai_100', 'mapel')) Schema::table('nilai_100', function (Blueprint $table) {
      $table->string('mapel', 20)->default('MAT')->after('student_id');
    });
  }
  public function down(): void
  {
    if (Schema::hasColumn('nilai_100', 'mapel')) Schema::table('nilai_100', function (Blueprint $table) {
      $table->dropColumn('mapel');
    });
  }
};
