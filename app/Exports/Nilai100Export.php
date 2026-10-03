<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{FromQuery, WithMapping};

class Nilai100Export extends BaseSheet implements FromQuery, WithMapping
{
  public function __construct(private Builder $records) {}

  public function title(): string
  {
    return 'Nilai 100';
  }

  public function headings(): array
  {
    return ['Noreg', 'Nama', 'Unit', 'Mapel', 'Jenis', 'Tanggal Ujian', 'Validasi PT', 'Testimoni', 'Keterangan'];
  }

  public function query(): Builder
  {
    return $this->records;
  }

  public function map($nilai): array
  {
    return [$nilai->student->noreg, $nilai->student->nama_siswa, $nilai->student->unit->nama_unit, $nilai->mapel, $nilai->jenis_nilai, $nilai->tanggal_ujian?->format('d/m/Y'), $nilai->tanggal_validasi_pt?->format('d/m/Y'), $nilai->status_testimoni, $nilai->keterangan];
  }
}
