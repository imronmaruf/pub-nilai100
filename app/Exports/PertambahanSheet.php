<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromGenerator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PertambahanSheet extends BaseSheet implements FromGenerator
{
  public function __construct(private Collection $dates, private array $unitRows) {}
  public function title(): string
  {
    return 'Pertambahan Periode';
  }
  public function headings(): array
  {
    return array_merge(['KOTA', 'UNIT'], $this->dates->map(fn($d) => $d->format('d/m/Y'))->all(), ['TOTAL']);
  }
  public function generator(): \Generator
  {
    foreach ($this->unitRows as $row) {
      yield array_merge([$row['kota'], $row['unit']], $row['counts'], [$row['total']]);
    }
    $sums = array_fill(0, $this->dates->count(), 0);
    foreach ($this->unitRows as $row) {
      foreach ($row['counts'] as $i => $c) {
        $sums[$i] += $c;
      }
    }
    yield array_merge(['TOTAL', ''], $sums, [array_sum(array_map(fn($row) => $row['total'], $this->unitRows))]);
  }
  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F1F5F9']]],
      $sheet->getHighestRow() => ['font' => ['bold' => true]],
    ];
  }
}
