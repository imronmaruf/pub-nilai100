<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
  protected $fillable = ['kode', 'nama'];
  public function nilai100(): HasMany
  {
    return $this->hasMany(Nilai100::class, 'mapel', 'kode');
  }
}
