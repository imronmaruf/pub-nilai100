<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\Access;

class StudentRequest extends FormRequest
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
      'noreg' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('students', 'noreg')->ignore($id)],
      'nama_siswa' => 'required|string|max:255',
      'asal_sekolah' => 'required|string|max:255',
      'kelas_di_go' => 'required|string|max:100',
      'tingkat_kelas' => 'required|string|max:50',
      'level' => ['required', Rule::in(['SD', 'SMP', 'SMA'])],
      'unit_id' => ['required', 'integer', Rule::in(app(Access::class)->units($this->user())->pluck('id')->all())]
    ];
  }
}
