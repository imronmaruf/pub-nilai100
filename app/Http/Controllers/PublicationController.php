<?php

namespace App\Http\Controllers;

use App\Services\{Access, PublicationService};
use App\Http\Requests\PublicationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Imports\PublicationWorkbook;
use App\Exports\ActivityTemplateExport;
use App\Exports\PublicationExport;
use Maatwebsite\Excel\Facades\Excel;

class PublicationController extends Controller
{
  public function __construct(private Access $access, private PublicationService $service) {}
  public function index(Request $r, string $channel)
  {
    $query = $this->filteredRecords($r, $channel);
    $perPage = in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10;
    $rows = $query->paginate($perPage)->withQueryString();
    $units = $this->access->units($r->user())->orderBy('nama_unit')->get();
    return view('publications.index', ['rows' => $rows, 'channel' => $channel, 'units' => $units, 'cities' => $units->pluck('kota')->unique()->sort()->values()]);
  }
  public function export(Request $r, string $channel)
  {
    return Excel::download(new PublicationExport($this->filteredRecords($r, $channel), $channel), 'Publikasi_' . strtoupper($channel) . '_' . now()->format('Ymd_His') . '.xlsx');
  }
  private function filteredRecords(Request $r, string $channel): Builder
  {
    foreach (['kota', 'unit_id'] as $key) if ($r->filled($key) && !is_array($r->input($key))) $r->merge([$key => [$r->input($key)]]);
    $filters = $r->validate(['q' => 'nullable|string|max:100', 'kota' => 'nullable|array', 'kota.*' => 'string|max:255', 'unit_id' => 'nullable|array', 'unit_id.*' => 'integer', 'status_publikasi' => 'nullable|in:SUDAH,BELUM', 'from' => 'nullable|date', 'to' => 'nullable|date', 'per_page' => 'nullable|in:10,20,30']);
    $q = trim((string)($filters['q'] ?? ''));
    $dateColumn = $channel === 'wa' ? 'tanggal_blast' : 'tanggal_posting';
    return $this->access->records($this->service->model($channel), $r->user())->with('student.unit', 'nilai100.mapelInfo')
      ->when($q !== '', fn($query) => $query->whereHas('student', fn($student) => $student->where('noreg', 'like', "%$q%")->orWhere('nama_siswa', 'like', "%$q%")))
      ->when($r->filled('kota'), fn($query) => $query->whereHas('student.unit', fn($unit) => $unit->whereIn('kota', $r->input('kota'))))
      ->when($r->filled('unit_id'), fn($query) => $query->whereHas('student', fn($student) => $student->whereIn('unit_id', $r->input('unit_id'))))
      ->when($channel === 'ig' && $r->filled('status_publikasi'), fn($query) => $query->where('status_publikasi', $r->input('status_publikasi')))
      ->when($r->filled('from'), fn($query) => $query->whereDate($dateColumn, '>=', $r->input('from')))
      ->when($r->filled('to'), fn($query) => $query->whereDate($dateColumn, '<=', $r->input('to')))
      ->latest();
  }
  public function importForm(string $channel)
  {
    $this->service->model($channel);
    return view('publications.import', compact('channel'));
  }
  public function template(string $channel)
  {
    $this->service->model($channel);
    return Excel::download(new ActivityTemplateExport($channel), 'template_import_' . $channel . '.xlsx');
  }
  public function import(Request $r, string $channel)
  {
    $this->service->model($channel);
    $r->validate(['file' => 'required|file|mimes:xlsx|max:5120']);
    DB::transaction(fn() => Excel::import(new PublicationWorkbook($r->user(), $channel), $r->file('file')));
    return to_route('publications.index', $channel)->with('status', 'Import publikasi selesai.');
  }
  public function create(string $channel)
  {
    $model = $this->service->model($channel);
    return view('publications.form', ['channel' => $channel, 'record' => new $model, 'student' => null]);
  }
  public function edit(Request $r, string $channel, string $publication)
  {
    $record = $this->access->records($this->service->model($channel), $r->user())->with('student.unit', 'student.nilai100.mapelInfo')->findOrFail($publication);
    return view('publications.form', ['channel' => $channel, 'record' => $record, 'student' => $record->student]);
  }
  public function store(PublicationRequest $r, string $channel)
  {
    return $this->save($r, $channel, null);
  }
  public function update(PublicationRequest $r, string $channel, string $publication)
  {
    return $this->save($r, $channel, $publication);
  }
  private function save(PublicationRequest $r, string $channel, ?string $id)
  {
    DB::transaction(function () use ($r, $channel, $id) {
      $model = $this->service->model($channel);
      $existing = $id ? $this->access->records($model, $r->user())->findOrFail($id) : null;
      $s = $this->access->students($r->user())->lockForUpdate()->findOrFail($existing?->student_id ?? $r->validated('student_id'));
      $gradeId = $r->validated('nilai100_id') ?? $existing?->nilai100_id;
      $this->service->eligible($s, $gradeId === null ? null : (int)$gradeId);
      $d = $r->safe()->except('student_id');
      if ($channel === 'ig' && $d['status_publikasi'] === 'BELUM') {
        $d['tanggal_posting'] = null;
        $d['link_postingan'] = null;
      }
      if ($id) $this->access->records($model, $r->user())->lockForUpdate()->findOrFail($id)->update($d);
      else $model::create($d + ['student_id' => $s->id]);
    });
    return to_route('publications.index', $channel)->with('status', 'Publikasi disimpan.');
  }
  public function destroy(Request $r, string $channel, string $publication)
  {
    DB::transaction(function () use ($r, $channel, $publication) {
      $model = $this->service->model($channel);
      $p = $this->access->records($model, $r->user())->findOrFail($publication);
      $this->access->students($r->user())->lockForUpdate()->findOrFail($p->student_id);
      $this->access->records($model, $r->user())->findOrFail($publication)->delete();
    });
    return back()->with('status', 'Publikasi dihapus.');
  }
}
