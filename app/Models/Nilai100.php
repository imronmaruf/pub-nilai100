<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai100 extends Model
{
  protected $table = 'nilai_100';
  public const MAPEL = ['MAT', 'FIS', 'KIM', 'BIO', 'GEO', 'SOS', 'INFOR', 'MAT-TL', 'ING-TL', 'SEJ', 'IPA', 'IPS', 'PKN', 'EKO', 'B.INDO', 'B.ING'];
  protected $fillable = ['student_id', 'mapel', 'jenis_nilai', 'tanggal_ujian', 'tanggal_validasi_pt', 'status_testimoni'];
  protected function casts(): array
  {
    return ['tanggal_ujian' => 'date', 'tanggal_validasi_pt' => 'date'];
  }
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }
}
