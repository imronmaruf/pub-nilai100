<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('students', function (Blueprint $table) {
      if (!Schema::hasColumn('students', 'tingkat_kelas')) $table->string('tingkat_kelas', 50)->default('')->after('kelas_di_go');
      if (!Schema::hasColumn('students', 'level')) $table->string('level', 10)->default('SMA')->after('tingkat_kelas');
    });
    $index = collect(Schema::getIndexes('students'))->contains(fn($item) => $item['name'] === 'students_unit_id_level_tingkat_kelas_index');
    if (!$index) Schema::table('students', fn(Blueprint $table) => $table->index(['unit_id', 'level', 'tingkat_kelas']));
  }
  public function down(): void
  {
    if (collect(Schema::getIndexes('students'))->contains(fn($item) => $item['name'] === 'students_unit_id_level_tingkat_kelas_index')) Schema::table('students', fn(Blueprint $table) => $table->dropIndex(['unit_id', 'level', 'tingkat_kelas']));
    Schema::table('students', function (Blueprint $table) {
      if (Schema::hasColumn('students', 'tingkat_kelas')) $table->dropColumn('tingkat_kelas');
      if (Schema::hasColumn('students', 'level')) $table->dropColumn('level');
    });
  }
};
