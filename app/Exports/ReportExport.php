<?php
namespace App\Exports;
use App\Models\User;
use App\Services\Access;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class ReportExport implements WithMultipleSheets {
 public function __construct(private User $actor){}
 public function sheets():array {
  $s=[new ResumeSheet($this->actor),new AllDataSheet($this->actor)];
  foreach(app(Access::class)->units($this->actor)->orderBy('id')->get()as$u){
   $safe=str_replace(['[',']',':','*','?','/','\\',"'"],' ',$u->nama_unit);
   $safe=preg_replace('/[\x00-\x1F]/u','',$safe);
   // Unique ID prefix prevents collisions with other units and reserved sheet names.
   $title=mb_substr($u->id.' '.trim($safe),0,31);
   $s[]=new StudentSheet($this->actor,$u->id,$title);
  }return $s;
 }
}
