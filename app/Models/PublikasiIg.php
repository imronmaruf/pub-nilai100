<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PublikasiIg extends Model {
 protected $table='publikasi_ig';
 protected $fillable=['student_id', 'status_publikasi', 'jumlah_postingan', 'tanggal_posting', 'link_postingan', 'view', 'like', 'komen'];
 protected function casts():array{return ['tanggal_posting'=>'date'];}
 public function student():BelongsTo{return $this->belongsTo(Student::class);}
}
