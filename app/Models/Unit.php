<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Unit extends Model {
 protected $fillable=['kota','nama_unit'];
 public function students():HasMany{return $this->hasMany(Student::class);}
 public function users():HasMany{return $this->hasMany(User::class);}
}
