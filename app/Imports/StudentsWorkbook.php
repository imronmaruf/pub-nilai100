<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\UnitReferenceImport;

class StudentsWorkbook implements WithMultipleSheets
{
  public function __construct(private User $actor) {}
  public function sheets(): array
  {
    return [0 => new StudentsImport($this->actor), 1 => new UnitReferenceImport($this->actor)];
  }
}
