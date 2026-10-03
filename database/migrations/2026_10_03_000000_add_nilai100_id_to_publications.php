<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    foreach (['publikasi_ig', 'publikasi_tiktok', 'publikasi_wa'] as $tableName) {
      Schema::table($tableName, function (Blueprint $table) {
        $table->foreignId('nilai100_id')->nullable()->after('student_id')->constrained('nilai_100')->nullOnDelete();
      });
    }
  }

  public function down(): void
  {
    foreach (['publikasi_wa', 'publikasi_tiktok', 'publikasi_ig'] as $tableName) {
      Schema::table($tableName, function (Blueprint $table) {
        $table->dropConstrainedForeignId('nilai100_id');
      });
    }
  }
};
