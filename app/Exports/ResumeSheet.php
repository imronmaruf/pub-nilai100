<?php
namespace App\Exports;
use App\Models\User;
use App\Services\ResumeService;
use Maatwebsite\Excel\Concerns\FromGenerator;
class ResumeSheet extends BaseSheet implements FromGenerator {
 public function __construct(private User $actor){}
 public function title():string{return 'Resume';}
 public function headings():array{return ResumeService::HEADINGS;}
 public function generator():\Generator{$service=app(ResumeService::class);$i=0;foreach($service->query($this->actor)->cursor()as$row)yield $service->row($row,++$i);}
}
