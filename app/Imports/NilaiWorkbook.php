<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NilaiWorkbook implements WithMultipleSheets
{
  public function __construct(private User $actor) {}

  public function sheets(): array
  {
    // Template punya sheet Panduan di index 1 — hanya impor sheet data.
    return [0 => new NilaiImport($this->actor)];
  }
}
