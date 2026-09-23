<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $t) {
            $t->id();
            $t->string('kota');
            $t->string('nama_unit');
            $t->timestamps();
            $t->unique(['kota', 'nama_unit']);
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->foreignId('unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->string('noreg', 50)->unique();
            $t->string('nama_siswa');
            $t->string('asal_sekolah');
            $t->string('kelas_di_go', 100);
            $t->string('tingkat_kelas', 50);
            $t->string('level', 10);
            $t->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $t->timestamps();
            $t->index(['unit_id', 'nama_siswa']);
        });
        Schema::create('nilai_100', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $t->string('mapel', 20);
            $t->enum('jenis_nilai', ['UH', 'PTS', 'PAS', 'PAT', 'US']);
            $t->date('tanggal_ujian')->nullable();
            $t->date('tanggal_validasi_pt')->nullable();
            $t->enum('status_testimoni', ['SUDAH', 'BELUM'])->default('BELUM');
            $t->text('keterangan')->nullable();
            $t->timestamps();
        });
        foreach (['publikasi_ig', 'publikasi_tiktok'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->id();
                $t->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                if ($table === 'publikasi_ig') $t->enum('status_publikasi', ['SUDAH', 'BELUM'])->default('BELUM');
                $t->unsignedInteger('jumlah_postingan')->default(0);
                $t->date('tanggal_posting')->nullable();
                $t->string('link_postingan', 2048)->nullable();
                foreach (['view', 'like', 'komen'] as $c) $t->unsignedBigInteger($c)->default(0);
                $t->timestamps();
                $t->index(['student_id', 'tanggal_posting']);
            });
        }
        Schema::create('publikasi_wa', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $t->unsignedInteger('jumlah_testimoni_terkirim')->default(0);
            $t->date('tanggal_blast');
            foreach (['terkirim', 'dibaca', 'respon'] as $c) $t->unsignedBigInteger($c)->default(0);
            $t->timestamps();
            $t->index(['student_id', 'tanggal_blast']);
        });
    }
    public function down(): void
    {
        foreach (['publikasi_wa', 'publikasi_tiktok', 'publikasi_ig', 'nilai_100', 'students', 'users', 'units'] as $t) Schema::dropIfExists($t);
    }
};
