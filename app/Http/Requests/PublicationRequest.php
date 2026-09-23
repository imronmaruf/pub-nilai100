<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\{Access, PublicationService};

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
    $rules = ['student_id' => [$this->route('publication') ? 'prohibited' : 'required', 'integer']];
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
      $id = $this->input('student_id');
      if ($this->route('publication')) $id = $access->records($service->model($this->route('channel')), $this->user())->findOrFail($this->route('publication'))->student_id;
      $s = $access->students($this->user())->with('nilai100')->findOrFail($id);
      if (!$s->nilai100->contains(fn($nilai) => $nilai->status_testimoni === 'SUDAH')) $v->errors()->add('student_id', 'Publikasi membutuhkan Nilai 100 dengan testimoni SUDAH.');
    }];
  }
}
