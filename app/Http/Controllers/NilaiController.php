<?php

namespace App\Http\Controllers;

use App\Models\Nilai100;
use App\Models\Mapel;
use App\Services\Access;
use App\Http\Requests\NilaiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Imports\NilaiImport;
use App\Exports\ActivityTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class NilaiController extends Controller
{
  public function __construct(private Access $access) {}
  public function index(Request $r)
  {
    foreach (['kota', 'unit_id', 'mapel', 'jenis_nilai', 'status_testimoni'] as $key) if ($r->filled($key) && !is_array($r->input($key))) $r->merge([$key => [$r->input($key)]]);
    $filters = $r->validate(['kota' => 'nullable|array', 'kota.*' => 'string', 'unit_id' => 'nullable|array', 'unit_id.*' => 'integer', 'mapel' => 'nullable|array', 'mapel.*' => 'string', 'jenis_nilai' => 'nullable|array', 'jenis_nilai.*' => 'in:UH,PTS,PAS,PAT,US', 'status_testimoni' => 'nullable|array', 'status_testimoni.*' => 'in:SUDAH,BELUM', 'from' => 'nullable|date', 'to' => 'nullable|date', 'per_page' => 'nullable|in:10,20,30']);
    $base = $this->access->records(Nilai100::class, $r->user())->with('student.unit');
    $query = (clone $base)
      ->when($r->filled('unit_id'), fn($q) => $q->whereHas('student', fn($s) => $s->whereIn('unit_id', $r->input('unit_id'))))
      ->when($r->filled('kota'), fn($q) => $q->whereHas('student.unit', fn($u) => $u->whereIn('kota', $r->input('kota'))))
      ->when($r->filled('mapel'), fn($q) => $q->whereIn('mapel', $r->input('mapel')))
      ->when($r->filled('jenis_nilai'), fn($q) => $q->whereIn('jenis_nilai', $r->input('jenis_nilai')))
      ->when($r->filled('status_testimoni'), fn($q) => $q->whereIn('status_testimoni', $r->input('status_testimoni')))
      ->when($r->filled('from'), fn($q) => $q->whereDate('tanggal_ujian', '>=', $r->input('from')))
      ->when($r->filled('to'), fn($q) => $q->whereDate('tanggal_ujian', '<=', $r->input('to')))
      ->latest();
    $summary = (clone $query)->reorder()->selectRaw("COUNT(DISTINCT student_id) as unique_students, COUNT(*) as total_values, COUNT(DISTINCT CASE WHEN status_testimoni = 'SUDAH' THEN student_id END) as testified, COUNT(DISTINCT CASE WHEN status_testimoni = 'BELUM' THEN student_id END) as pending")->first();
    $rows = $query->paginate($r->integer('per_page') && in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10)->withQueryString();
    $units = $this->access->units($r->user())->orderBy('nama_unit')->get();
    return view('nilai.index', ['rows' => $rows, 'summary' => $summary, 'units' => $units, 'cities' => $units->pluck('kota')->unique()->sort()->values(), 'mapels' => Mapel::orderBy('kode')->get()]);
  }
  public function importForm()
  {
    return view('nilai.import');
  }
  public function template()
  {
    return Excel::download(new ActivityTemplateExport('nilai'), 'template_import_nilai_100.xlsx');
  }
  public function import(Request $r)
  {
    $r->validate(['file' => 'required|file|mimes:xlsx|max:5120']);
    DB::transaction(fn() => Excel::import(new NilaiImport($r->user()), $r->file('file')));
    return to_route('nilai.index')->with('status', 'Import Nilai 100 selesai.');
  }
  public function create()
  {
    return view('nilai.form', ['student' => null, 'nilai' => new Nilai100, 'mapels' => Mapel::orderBy('kode')->get()]);
  }
  public function edit(Request $r, string $student)
  {
    $s = $this->access->students($r->user())->with(['unit', 'nilai100'])->findOrFail($student);
    $nilai = $s->nilai100->sortByDesc('tanggal_ujian')->first();
    abort_unless($nilai, 404);
    return view('nilai.form', ['student' => $s, 'nilai' => $nilai, 'mapels' => Mapel::orderBy('kode')->get()]);
  }
  public function store(NilaiRequest $r)
  {
    return $this->save($r, null);
  }
  public function update(NilaiRequest $r, string $student)
  {
    return $this->save($r, $student);
  }
  private function save(NilaiRequest $r, ?string $id)
  {
    DB::transaction(function () use ($r, $id) {
      $s = $this->access->students($r->user())->lockForUpdate()->findOrFail($id ?? $r->validated('student_id'));
      $n = $s->nilai100()->first();
      if ($id) abort_unless($n, 404);
      if (!$id && $s->nilai100()->where('mapel', $r->validated('mapel'))->where('jenis_nilai', $r->validated('jenis_nilai'))->whereDate('tanggal_ujian', $r->validated('tanggal_ujian'))->exists()) throw ValidationException::withMessages(['student_id' => 'Nilai dengan mapel, jenis, dan tanggal tersebut sudah ada.']);
      if ($r->validated('status_testimoni') === 'BELUM' && $s->hasPublications()) throw ValidationException::withMessages(['status_testimoni' => 'Hapus catatan publikasi sebelum mengubah testimoni menjadi BELUM.']);
      $data = Nilai100::withValidasiNote($r->safe()->except('student_id')->all());
      if ($n && $id) $n->update($data);
      else $s->nilai100()->create($data);
    });
    return to_route('nilai.index')->with('status', 'Nilai disimpan.');
  }
  public function destroy(Request $r, string $student)
  {
    DB::transaction(function () use ($r, $student) {
      $s = $this->access->students($r->user())->lockForUpdate()->findOrFail($student);
      if ($s->hasPublications()) throw ValidationException::withMessages(['nilai' => 'Hapus publikasi siswa terlebih dahulu.']);
      $s->nilai100()->firstOrFail()->delete();
    });
    return back()->with('status', 'Nilai dihapus.');
  }
}
