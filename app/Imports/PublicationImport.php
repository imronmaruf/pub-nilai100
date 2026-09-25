<?php

namespace App\Imports;

use App\Models\{PublikasiIg, PublikasiTiktok, PublikasiWa};
use App\Services\Access;
use App\Support\{ImportDate, ImportHeaders};
use Illuminate\Support\{Collection, Facades\Validator};
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PublicationImport implements ToCollection, WithCalculatedFormulas
{
  public function __construct(private $actor, private string $channel) {}

  public function collection(Collection $rows): void
  {
    $expected = $this->channel === 'wa'
      ? ['noreg', 'jumlah testimoni terkirim', 'tanggal blast', 'terkirim', 'dibaca', 'respon']
      : ['noreg', ...($this->channel === 'ig' ? ['status publikasi'] : []), 'jumlah postingan', 'tanggal posting', 'link postingan', 'view', 'like', 'komen'];

    $head = $rows->shift();
    if (!ImportHeaders::match($head, $expected)) {
      throw ValidationException::withMessages(['file' => 'Header template ' . $this->channel . ' tidak sesuai.']);
    }

    $students = app(Access::class)->students($this->actor)->with('nilai100')->get()->keyBy(fn($s) => strtolower($s->noreg));
    $records = [];

    foreach ($rows as $i => $row) {
      if ($row->filter(fn($v) => $v !== null && trim((string)$v) !== '')->isEmpty()) {
        continue;
      }

      $student = $students->get(strtolower(trim((string)($row[0] ?? ''))));
      $data = ['student_id' => $student?->id];

      if (!$student || !$student->nilai100->contains(fn($n) => $n->status_testimoni === 'SUDAH')) {
        throw ValidationException::withMessages(['file' => 'Baris ' . ($i + 2) . ': Noreg tidak ditemukan atau belum memiliki testimoni SUDAH.']);
      }

      if ($this->channel === 'wa') {
        $data += [
          'jumlah_testimoni_terkirim' => $row[1] ?? null,
          'tanggal_blast' => ImportDate::normalize($row[2] ?? null),
          'terkirim' => $row[3] ?? null,
          'dibaca' => $row[4] ?? null,
          'respon' => $row[5] ?? null,
        ];
        $rules = [
          'jumlah_testimoni_terkirim' => 'required|integer|min:1',
          'tanggal_blast' => 'required|date_format:Y-m-d|before_or_equal:today',
          'terkirim' => 'required|integer|min:0',
          'dibaca' => 'required|integer|min:0|lte:terkirim',
          'respon' => 'required|integer|min:0|lte:dibaca',
        ];
      } else {
        $offset = $this->channel === 'ig' ? 1 : 0;
        $data += ($this->channel === 'ig' ? ['status_publikasi' => strtoupper(trim((string)($row[1] ?? '')))] : []) + [
          'jumlah_postingan' => $row[1 + $offset] ?? null,
          'tanggal_posting' => ImportDate::normalize($row[2 + $offset] ?? null),
          'link_postingan' => $row[3 + $offset] ?? null,
          'view' => $row[4 + $offset] ?? null,
          'like' => $row[5 + $offset] ?? null,
          'komen' => $row[6 + $offset] ?? null,
        ];
        $rules = [
          'jumlah_postingan' => 'required|integer|min:1',
          'tanggal_posting' => 'required|date_format:Y-m-d|before_or_equal:today',
          'link_postingan' => 'required|url:http,https|max:2048',
          'view' => 'required|integer|min:0',
          'like' => 'required|integer|min:0',
          'komen' => 'required|integer|min:0',
        ];
        if ($this->channel === 'ig') {
          $rules['status_publikasi'] = 'required|in:SUDAH';
        }
      }

      $v = Validator::make($data, $rules);
      if ($v->fails()) {
        throw ValidationException::withMessages(['file' => 'Baris ' . ($i + 2) . ': ' . implode(' ', $v->errors()->all())]);
      }

      $records[] = $data;
    }

    if (!$records) {
      throw ValidationException::withMessages(['file' => 'Tidak ada baris data.']);
    }

    $model = match ($this->channel) {
      'ig' => PublikasiIg::class,
      'tiktok' => PublikasiTiktok::class,
      'wa' => PublikasiWa::class,
    };

    foreach ($records as $data) {
      $model::create($data);
    }
  }
}
