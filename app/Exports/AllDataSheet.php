<?php
namespace App\Exports;
// One row per publication event, or one base row for a student without publications.
// Never join several hasMany relations together: that multiplies raw events.
class AllDataSheet extends StudentSheet {
 public function headings():array{return array_merge(parent::headings(),['Kanal','Publikasi ID','Status IG','Jumlah Post','Tanggal Posting','Link','View','Like','Komen','Jumlah Testimoni Terkirim','Tanggal Blast','Terkirim','Dibaca','Respon']);}
 public function generator():\Generator {
  foreach($this->query()->lazyById(250)as$s){$emitted=false;
   foreach(['IG'=>'instagram','TikTok'=>'tiktok','WA'=>'whatsapp']as$channel=>$rel){
    foreach($s->$rel()->orderBy('id')->lazyById(250)as$p){$emitted=true;yield array_merge($this->base($s),[$channel,$p->id,$p->status_publikasi,$p->jumlah_postingan,$p->tanggal_posting?->format('Y-m-d'),$p->link_postingan,$p->view,$p->like,$p->komen,$p->jumlah_testimoni_terkirim,$p->tanggal_blast?->format('Y-m-d'),$p->terkirim,$p->dibaca,$p->respon]);}
   }
   if(!$emitted)yield array_merge($this->base($s),array_fill(0,14,null));
  }
 }
}
