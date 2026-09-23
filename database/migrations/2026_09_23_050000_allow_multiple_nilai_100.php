<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('nilai_100', function (Blueprint $table) {
      $table->index('student_id', 'nilai_100_student_id_index');
      $table->dropUnique('nilai_100_student_id_unique');
    });
  }
  public function down(): void
  {
    Schema::table('nilai_100', function (Blueprint $table) {
      $table->unique('student_id');
    });
  }
};
