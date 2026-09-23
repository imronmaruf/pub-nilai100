<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Student extends Model
{
  protected $fillable = ['noreg', 'nama_siswa', 'asal_sekolah', 'kelas_di_go', 'tingkat_kelas', 'level', 'unit_id'];
  public function unit(): BelongsTo
  {
    return $this->belongsTo(Unit::class);
  }
  public function nilai100(): HasMany
  {
    return $this->hasMany(Nilai100::class);
  }
  public function instagram(): HasMany
  {
    return $this->hasMany(PublikasiIg::class);
  }
  public function tiktok(): HasMany
  {
    return $this->hasMany(PublikasiTiktok::class);
  }
  public function whatsapp(): HasMany
  {
    return $this->hasMany(PublikasiWa::class);
  }
  public function hasPublications(): bool
  {
    return $this->instagram()->exists() || $this->tiktok()->exists() || $this->whatsapp()->exists();
  }
}
