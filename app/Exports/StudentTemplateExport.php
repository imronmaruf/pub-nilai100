<?php

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\{WithMultipleSheets, FromArray, WithHeadings, WithTitle};

class StudentTemplateExport implements WithMultipleSheets
{
  public function sheets(): array
  {
    return [new StudentTemplateSheet, new StudentGuideSheet, new UnitReferenceSheet];
  }
}
class StudentTemplateSheet implements FromArray, WithHeadings, WithTitle
{
  public function title(): string
  {
    return 'Data Siswa';
  }
  public function headings(): array
  {
    return ['Noreg', 'Nama', 'Asal Sekolah', 'Tingkat Kelas', 'Kelas di GO', 'Level', 'Unit'];
  }
  public function array(): array
  {
    return [];
  }
}
class StudentGuideSheet implements FromArray, WithTitle
{
  public function title(): string
  {
    return 'Panduan';
  }
  public function array(): array
  {
    return [
      ['PANDUAN PENGISIAN DATA SISWA'],
      ['Isi data pada sheet Data Siswa.'],
      ['Noreg wajib unik. Data dengan Noreg yang sudah ada akan dilewati saat import.'],
      ['Tingkat Kelas dan Kelas di GO diisi sesuai data siswa.'],
      ['Level yang boleh diisi: SD, SMP, atau SMA.'],
      ['Unit diisi menggunakan ID Unit yang tercantum pada sheet Referensi Unit.'],
      ['Jangan mengubah nama atau urutan header.'],
      ['Simpan file dalam format .xlsx sebelum diupload.']
    ];
  }
}
class UnitReferenceSheet implements FromArray, WithHeadings, WithTitle
{
  public function title(): string
  {
    return 'Referensi Unit';
  }
  public function headings(): array
  {
    return ['ID Unit', 'Kota', 'Unit'];
  }
  public function array(): array
  {
    return Unit::orderBy('id')->get(['id', 'kota', 'nama_unit'])->map(fn($unit) => [$unit->id, $unit->kota, $unit->nama_unit])->all();
  }
}
