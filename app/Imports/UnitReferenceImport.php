<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UnitReferenceImport implements ToCollection
{
  public function __construct(private User $actor) {}
  public function collection(Collection $rows): void {}
}
