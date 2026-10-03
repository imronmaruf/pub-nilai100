<?php

namespace App\Services;

use App\Models\{PublikasiIg, PublikasiTiktok, PublikasiWa, Student};
use Illuminate\Validation\ValidationException;

class PublicationService
{
  public function model(string $channel): string
  {
    return match ($channel) {
      'ig' => PublikasiIg::class,
      'tiktok' => PublikasiTiktok::class,
      'wa' => PublikasiWa::class,
      default => abort(404)
    };
  }
  public function eligible(Student $s, ?int $nilai100Id = null): void
  {
    $query = $s->nilai100()->where('status_testimoni', 'SUDAH');
    if ($nilai100Id !== null) $query->whereKey($nilai100Id);
    if (!$query->exists()) throw ValidationException::withMessages([$nilai100Id !== null ? 'nilai100_id' : 'student_id' => 'Publikasi membutuhkan Nilai 100 terpilih dengan testimoni SUDAH.']);
  }
}
