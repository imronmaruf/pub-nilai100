<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{FromQuery, WithMapping};

class StudentListExport extends BaseSheet implements FromQuery, WithMapping
{
  public function __construct(private Builder $students) {}

  public function title(): string
  {
    return 'Data Siswa';
  }

  public function headings(): array
  {
    return ['Noreg', 'Nama', 'Asal Sekolah', 'Unit', 'Kelas di GO', 'Tingkat', 'Level', 'Jumlah Nilai 100'];
  }

  public function query(): Builder
  {
    return $this->students;
  }

  public function map($student): array
  {
    return [$student->noreg, $student->nama_siswa, $student->asal_sekolah, $student->unit->nama_unit, $student->kelas_di_go, $student->tingkat_kelas, $student->level, $student->nilai100_count ?: 'BELUM'];
  }
}
