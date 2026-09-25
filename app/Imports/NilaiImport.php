<?php

namespace App\Imports;

use App\Models\{Nilai100, Mapel};
use App\Services\Access;
use App\Support\{ImportDate, ImportHeaders};
use Illuminate\Support\{Collection, Facades\Validator};
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class NilaiImport implements ToCollection, WithCalculatedFormulas
{
  public function __construct(private $actor) {}

  public function collection(Collection $rows): void
  {
    $head = $rows->shift();
    $expected = ['noreg', 'mapel', 'jenis nilai', 'tanggal ujian', 'tanggal validasi pt', 'status testimoni', 'keterangan'];

    if (!ImportHeaders::match($head, $expected)) {
      throw ValidationException::withMessages(['file' => 'Header harus: Noreg, Mapel, Jenis Nilai, Tanggal Ujian, Tanggal Validasi PT, Status Testimoni, Keterangan.']);
    }

    $students = app(Access::class)->students($this->actor)->get()->keyBy(fn($s) => strtolower($s->noreg));
    $records = [];
    $mapels = Mapel::pluck('kode')->all();

    foreach ($rows as $i => $row) {
      if ($row->filter(fn($v) => $v !== null && trim((string)$v) !== '')->isEmpty()) {
        continue;
      }

      $student = $students->get(strtolower(trim((string)($row[0] ?? ''))));
      $tglUjian = ImportDate::normalize($row[3] ?? null);
      $tglValidasiPt = ImportDate::normalize($row[4] ?? null);
      $tglUjian = ($tglUjian === null || trim((string)$tglUjian) === '') ? null : $tglUjian;
      $tglValidasiPt = ($tglValidasiPt === null || trim((string)$tglValidasiPt) === '') ? null : $tglValidasiPt;

      $data = [
        'student_id' => $student?->id,
        'mapel' => strtoupper(trim((string)($row[1] ?? ''))),
        'jenis_nilai' => strtoupper(trim((string)($row[2] ?? ''))),
        'tanggal_ujian' => $tglUjian,
        'tanggal_validasi_pt' => $tglValidasiPt,
        'status_testimoni' => strtoupper(trim((string)($row[5] ?? ''))),
        'keterangan' => trim((string)($row[6] ?? '')) ?: null,
      ];

      $rules = [
        'student_id' => 'required',
        'mapel' => ['required', Rule::in($mapels)],
        'jenis_nilai' => 'required|in:UH,PTS,PAS,PAT,US',
        'tanggal_ujian' => 'nullable|date_format:Y-m-d|before_or_equal:today',
        'tanggal_validasi_pt' => array_values(array_filter([
          'nullable',
          'date_format:Y-m-d',
          'before_or_equal:today',
          $data['tanggal_ujian'] ? 'after_or_equal:tanggal_ujian' : null,
        ])),
        'status_testimoni' => 'required|in:SUDAH,BELUM',
        'keterangan' => 'nullable|string|max:1000',
      ];

      $v = Validator::make($data, $rules);
      if ($v->fails()) {
        throw ValidationException::withMessages(['file' => 'Baris ' . ($i + 2) . ': ' . implode(' ', $v->errors()->all())]);
      }

      $records[] = Nilai100::withValidasiNote($data);
    }

    if (!$records) {
      throw ValidationException::withMessages(['file' => 'Tidak ada baris data.']);
    }

    foreach ($records as $data) {
      Nilai100::create($data);
    }
  }
}
