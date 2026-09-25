<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PublicationWorkbook implements WithMultipleSheets
{
  public function __construct(private User $actor, private string $channel) {}

  public function sheets(): array
  {
    // Template punya sheet Panduan di index 1 — hanya impor sheet data.
    return [0 => new PublicationImport($this->actor, $this->channel)];
  }
}
