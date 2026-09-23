<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PublikasiWa extends Model {
 protected $table='publikasi_wa';
 protected $fillable=['student_id', 'jumlah_testimoni_terkirim', 'tanggal_blast', 'terkirim', 'dibaca', 'respon'];
 protected function casts():array{return ['tanggal_blast'=>'date'];}
 public function student():BelongsTo{return $this->belongsTo(Student::class);}
}
