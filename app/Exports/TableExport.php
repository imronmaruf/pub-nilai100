<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class TableExport extends BaseSheet implements FromArray
{
  public function __construct(private string $sheet, private array $columns, private array $rows) {}

  public function title(): string
  {
    return $this->sheet;
  }

  public function headings(): array
  {
    return $this->columns;
  }

  public function array(): array
  {
    return $this->rows;
  }
}
