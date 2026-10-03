<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\{Access, PublicationService};
use App\Models\Nilai100;

class PublicationRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }
  public function rules(): array
  {
    $channel = $this->route('channel');
    app(PublicationService::class)->model($channel);
    $rules = [
      'student_id' => [$this->route('publication') ? 'prohibited' : 'required', 'integer'],
      'nilai100_id' => [$this->route('publication') ? 'nullable' : 'required', 'integer', Rule::exists('nilai_100', 'id')]
    ];
    if ($channel === 'wa') return $rules + [
      'jumlah_testimoni_terkirim' => 'required|integer|min:1|max:1000000',
      'tanggal_blast' => 'required|date_format:Y-m-d|before_or_equal:today',
      'terkirim' => 'required|integer|min:0|max:1000000000',
      'dibaca' => 'required|integer|min:0|lte:terkirim',
      'respon' => 'required|integer|min:0|lte:dibaca'
    ];
    $draft = $channel === 'ig' && $this->input('status_publikasi') === 'BELUM';
    if ($channel === 'ig') $rules['status_publikasi'] = ['required', Rule::in(['SUDAH', 'BELUM'])];
    $rules += [
      'jumlah_postingan' => $draft ? 'required|integer|in:0' : 'required|integer|min:1|max:1000000',
      'tanggal_posting' => $draft ? 'prohibited' : 'required|date_format:Y-m-d|before_or_equal:today',
      'link_postingan' => $draft ? 'prohibited' : 'required|url:http,https|max:2048'
    ];
    foreach (['view', 'like', 'komen'] as $f) $rules[$f] = $draft ? 'required|integer|in:0' : 'required|integer|min:0|max:1000000000';
    return $rules;
  }
  public function after(): array
  {
    return [function ($v) {
      if ($v->errors()->isNotEmpty()) return;
      $service = app(PublicationService::class);
      $access = app(Access::class);
      $record = $this->route('publication')
        ? $access->records($service->model($this->route('channel')), $this->user())->findOrFail($this->route('publication'))
        : null;
      $studentId = $record?->student_id ?? $this->input('student_id');
      $student = $access->students($this->user())->findOrFail($studentId);
      $gradeId = $this->input('nilai100_id', $record?->nilai100_id);
      if ($gradeId === null || $gradeId === '') {
        if (!$record) $v->errors()->add('nilai100_id', 'Pilih Nilai 100 yang akan dipublikasikan.');
        elseif ($record->nilai100_id !== null) $v->errors()->add('nilai100_id', 'Pilih Nilai 100 yang akan dipublikasikan.');
        else $service->eligible($student);
        return;
      }
      $grade = $access->records(Nilai100::class, $this->user())->where('student_id', $student->id)->find($gradeId);
      if (!$grade) {
        $v->errors()->add('nilai100_id', 'Nilai 100 tidak sesuai dengan siswa yang dipilih.');
        return;
      }
      $service->eligible($student, (int)$grade->id);
    }];
  }
}
