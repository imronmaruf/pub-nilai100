<?php

namespace App\Imports;

use App\Models\{User, Student};
use App\Services\Access;
use Illuminate\Support\{Collection, Facades\Validator};
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection
{
  public function __construct(private User $actor) {}
  public function collection(Collection $rows)
  {
    $head = $rows->shift();
    $headers = $head?->map(fn($v) => strtolower(trim((string)$v)))->values()->all() ?? [];
    $legacy = $headers === ['noreg', 'nama', 'asal sekolah', 'kelas', 'unit'];
    $modern = $headers === ['noreg', 'nama', 'asal sekolah', 'tingkat kelas', 'kelas di go', 'level', 'unit'];
    if (!$legacy && !$modern) throw ValidationException::withMessages(['file' => 'Header harus berurutan: Noreg, Nama, Asal Sekolah, Tingkat Kelas, Kelas di GO, Level, Unit.']);
    if ($rows->count() > 5000) throw ValidationException::withMessages(['file' => 'Maksimal 5.000 baris per import.']);
    $units = app(Access::class)->units($this->actor)->get();
    $seen = app(Access::class)->students($this->actor)->pluck('noreg')->mapWithKeys(fn($n) => [strtolower(trim($n)) => true])->all();
    $records = [];
    foreach ($rows as $i => $row) {
      if ($row->filter(fn($v) => $v !== null && trim((string)$v) !== '')->isEmpty()) continue;
      $unitColumn = $legacy ? 4 : 6;
      $raw = trim((string)($row[$unitColumn] ?? ''));
      $unit = ctype_digit($raw) ? $units->firstWhere('id', (int)$raw) : null;
      if (!$unit) {
        $matches = $units->filter(fn($u) => mb_strtolower($u->nama_unit) === mb_strtolower($raw));
        if ($matches->count() === 1) $unit = $matches->first();
      }
      $d = ['noreg' => trim((string)($row[0] ?? '')), 'nama_siswa' => trim((string)($row[1] ?? '')), 'asal_sekolah' => trim((string)($row[2] ?? '')), 'kelas_di_go' => $legacy ? trim((string)($row[3] ?? '')) : trim((string)($row[4] ?? '')), 'tingkat_kelas' => $legacy ? trim((string)($row[3] ?? '')) : trim((string)($row[3] ?? '')), 'level' => $legacy ? 'SMA' : strtoupper(trim((string)($row[5] ?? ''))), 'unit_id' => $unit?->id];
      $v = Validator::make($d, ['noreg' => ['required', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:students,noreg'], 'nama_siswa' => 'required|max:255', 'asal_sekolah' => 'required|max:255', 'kelas_di_go' => 'required|max:100', 'tingkat_kelas' => 'required|max:50', 'level' => ['required', 'in:SD,SMP,SMA'], 'unit_id' => 'required']);
      $key = strtolower($d['noreg']);
      if (isset($seen[$key])) continue;
      if ($v->fails()) throw ValidationException::withMessages(['file' => 'Baris ' . ($i + 2) . ': ' . implode(' ', $v->errors()->all()) . ' Unit harus termasuk unit yang boleh diakses.']);
      $seen[$key] = true;
      $records[] = $d;
    }
    if (!$records) throw ValidationException::withMessages(['file' => 'Tidak ada baris data.']);
    // Entire workbook transaction belongs to controller: any failure rolls back all rows.
    foreach ($records as $d) Student::create($d);
  }
}
