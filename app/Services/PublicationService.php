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
  public function eligible(Student $s): void
  {
    if (!$s->nilai100()->where('status_testimoni', 'SUDAH')->exists()) throw ValidationException::withMessages(['student_id' => 'Publikasi membutuhkan Nilai 100 dengan testimoni SUDAH.']);
  }
}
