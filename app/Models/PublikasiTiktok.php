<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PublikasiTiktok extends Model {
 protected $table='publikasi_tiktok';
 protected $fillable=['student_id', 'jumlah_postingan', 'tanggal_posting', 'link_postingan', 'view', 'like', 'komen'];
 protected function casts():array{return ['tanggal_posting'=>'date'];}
 public function student():BelongsTo{return $this->belongsTo(Student::class);}
}
