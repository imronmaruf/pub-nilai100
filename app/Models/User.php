<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable {
 use Notifiable,HasRoles;
 protected $fillable=['name','email','password','unit_id'];
 protected $hidden=['password','remember_token'];
 protected function casts():array{return ['password'=>'hashed','email_verified_at'=>'datetime'];}
 public function unit():BelongsTo{return $this->belongsTo(Unit::class);}
}
