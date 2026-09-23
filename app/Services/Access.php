<?php
namespace App\Services;
use App\Models\{User,Student,Unit};
use Illuminate\Database\Eloquent\Builder;
// Explicit actor: also safe for CLI exports/jobs. No dependency on ambient auth().
class Access {
 public function valid(User $u):void {abort_unless($u->hasRole('Superadmin')||($u->hasRole('Admin Unit')&&$u->unit_id!==null),403);}
 public function units(User $u):Builder {$this->valid($u);return Unit::query()->when(!$u->hasRole('Superadmin'),fn($q)=>$q->whereKey($u->unit_id));}
 public function students(User $u):Builder {$this->valid($u);return Student::query()->when(!$u->hasRole('Superadmin'),fn($q)=>$q->where('unit_id',$u->unit_id));}
 public function records(string $model,User $u):Builder {return $model::query()->whereIn('student_id',$this->students($u)->select('id'));}
}
