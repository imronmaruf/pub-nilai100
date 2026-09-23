<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\Access;

class NilaiRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }
  public function rules(): array
  {
    $id = $this->route('student');
    if ($id) app(Access::class)->students($this->user())->findOrFail($id);
    return [
      'student_id' => [$id ? 'prohibited' : 'required', 'integer'],
      'mapel' => ['required', Rule::in(\App\Models\Nilai100::MAPEL)],
      'jenis_nilai' => ['required', Rule::in(['UH', 'PTS', 'PAS', 'PAT', 'US'])],
      'tanggal_ujian' => 'required|date_format:Y-m-d|before_or_equal:today',
      'tanggal_validasi_pt' => 'required|date_format:Y-m-d|after_or_equal:tanggal_ujian|before_or_equal:today',
      'status_testimoni' => ['required', Rule::in(['SUDAH', 'BELUM'])]
    ];
  }
  public function after(): array
  {
    return [function ($v) {
      if (!$this->route('student') && !$v->errors()->has('student_id')) app(Access::class)->students($this->user())->findOrFail($this->input('student_id'));
    }];
  }
}
