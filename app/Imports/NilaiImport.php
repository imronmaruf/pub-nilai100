<?php

namespace App\Imports;

use App\Models\{Nilai100, Student, Mapel};
use App\Services\Access;
use Illuminate\Support\{Collection, Facades\Validator};
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Support\ImportDate;

class NilaiImport implements ToCollection
{
  public function __construct(private $actor) {}
  public function collection(Collection $rows): void
  {
    $head = $rows->shift();
    $expected = ['noreg', 'mapel', 'jenis nilai', 'tanggal ujian', 'tanggal validasi pt', 'status testimoni', 'keterangan'];
    if ($head?->map(fn($v) => strtolower(trim((string)$v)))->values()->all() !== $expected) throw ValidationException::withMessages(['file' => 'Header harus: Noreg, Mapel, Jenis Nilai, Tanggal Ujian, Tanggal Validasi PT, Status Testimoni, Keterangan.']);
    $students = app(Access::class)->students($this->actor)->get()->keyBy(fn($s) => strtolower($s->noreg));
    $records = [];
    foreach ($rows as $i => $row) {
      if ($row->filter(fn($v) => $v !== null && trim((string)$v) !== '')->isEmpty()) continue;
      $key = strtolower(trim((string)($row[0] ?? '')));
      $student = $students->get($key);
      $data = ['student_id' => $student?->id, 'mapel' => strtoupper(trim((string)($row[1] ?? ''))), 'jenis_nilai' => strtoupper(trim((string)($row[2] ?? ''))), 'tanggal_ujian' => ImportDate::normalize($row[3] ?? null), 'tanggal_validasi_pt' => ImportDate::normalize($row[4] ?? null), 'status_testimoni' => strtoupper(trim((string)($row[5] ?? ''))), 'keterangan' => trim((string)($row[6] ?? '')) ?: null];
      $v = Validator::make($data, ['student_id' => 'required', 'mapel' => ['required', Rule::in(Mapel::pluck('kode')->all())], 'jenis_nilai' => 'required|in:UH,PTS,PAS,PAT,US', 'tanggal_ujian' => 'nullable|date_format:Y-m-d|before_or_equal:today', 'tanggal_validasi_pt' => 'nullable|date_format:Y-m-d|after_or_equal:tanggal_ujian|before_or_equal:today', 'status_testimoni' => 'required|in:SUDAH,BELUM', 'keterangan' => 'nullable|string|max:1000']);
      if ($v->fails()) throw ValidationException::withMessages(['file' => 'Baris ' . ($i + 2) . ': ' . implode(' ', $v->errors()->all())]);
      $records[] = $data;
    }
    if (!$records) throw ValidationException::withMessages(['file' => 'Tidak ada baris data.']);
    foreach ($records as $data) Nilai100::create($data);
  }
}
