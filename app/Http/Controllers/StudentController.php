<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\Access;
use App\Http\Requests\StudentRequest;
use App\Imports\StudentsWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentTemplateExport;

class StudentController extends Controller
{
  public function __construct(private Access $access) {}
  public function index(Request $r)
  {
    foreach (['kota', 'unit_id', 'asal_sekolah', 'kelas_di_go', 'tingkat_kelas', 'level', 'nilai_status'] as $key) {
      if ($r->filled($key) && !is_array($r->input($key))) $r->merge([$key => [$r->input($key)]]);
    }
    $filters = $r->validate(['q' => 'nullable|string|max:100', 'kota' => 'nullable|array', 'kota.*' => 'string|max:255', 'unit_id' => 'nullable|array', 'unit_id.*' => 'integer', 'asal_sekolah' => 'nullable|array', 'asal_sekolah.*' => 'string|max:255', 'kelas_di_go' => 'nullable|array', 'kelas_di_go.*' => 'string|max:100', 'tingkat_kelas' => 'nullable|array', 'tingkat_kelas.*' => 'string|max:50', 'level' => 'nullable|array', 'level.*' => 'in:SD,SMP,SMA', 'nilai_status' => 'nullable|array', 'nilai_status.*' => 'in:sudah,belum', 'per_page' => 'nullable|in:10,20,30']);
    $base = $this->access->students($r->user());
    $q = trim((string)($filters['q'] ?? ''));
    $students = (clone $base)->with('unit')->withCount('nilai100')->when($q !== '', fn($b) => $b->where(fn($b) => $b->where('noreg', 'like', "%$q%")->orWhere('nama_siswa', 'like', "%$q%")))->when($r->filled('kota'), fn($b) => $b->whereHas('unit', fn($u) => $u->whereIn('kota', $r->input('kota'))))->when($r->filled('unit_id'), fn($b) => $b->whereIn('unit_id', $r->input('unit_id')))->when($r->filled('asal_sekolah'), fn($b) => $b->whereIn('asal_sekolah', $r->input('asal_sekolah')))->when($r->filled('kelas_di_go'), fn($b) => $b->whereIn('kelas_di_go', $r->input('kelas_di_go')))->when($r->filled('tingkat_kelas'), fn($b) => $b->whereIn('tingkat_kelas', $r->input('tingkat_kelas')))->when($r->filled('level'), fn($b) => $b->whereIn('level', $r->input('level')))->when($r->filled('nilai_status') && count($r->input('nilai_status')) === 1 && in_array('sudah', $r->input('nilai_status'), true), fn($b) => $b->whereHas('nilai100'))->when($r->filled('nilai_status') && count($r->input('nilai_status')) === 1 && in_array('belum', $r->input('nilai_status'), true), fn($b) => $b->whereDoesntHave('nilai100'))->orderBy('nama_siswa')->paginate((int)($filters['per_page'] ?? 10))->withQueryString();
    $all = (clone $base)->with('unit')->get(['id', 'unit_id', 'asal_sekolah', 'kelas_di_go', 'tingkat_kelas', 'level']);
    return view('students.index', ['students' => $students, 'q' => $q, 'units' => $this->access->units($r->user())->orderBy('nama_unit')->get(), 'cities' => $all->pluck('unit.kota')->filter()->unique()->sort()->values(), 'schools' => $all->pluck('asal_sekolah')->filter()->unique()->sort()->values(), 'goClasses' => $all->pluck('kelas_di_go')->filter()->unique()->sort()->values(), 'grades' => $all->pluck('tingkat_kelas')->filter()->unique()->sort()->values(), 'filters' => $filters]);
  }
  public function create(Request $r)
  {
    return view('students.form', ['student' => new Student, 'units' => $this->access->units($r->user())->orderBy('nama_unit')->get()]);
  }
  public function store(StudentRequest $r)
  {
    Student::create($r->validated());
    return to_route('students.index')->with('status', 'Siswa ditambahkan.');
  }
  public function edit(Request $r, string $student)
  {
    return view('students.form', ['student' => $this->access->students($r->user())->findOrFail($student), 'units' => $this->access->units($r->user())->orderBy('nama_unit')->get()]);
  }
  public function update(StudentRequest $r, string $student)
  {
    DB::transaction(function () use ($r, $student) {
      $this->access->students($r->user())->lockForUpdate()->findOrFail($student)->update($r->validated());
    });
    return to_route('students.index')->with('status', 'Siswa diperbarui.');
  }
  public function destroy(Request $r, string $student)
  {
    DB::transaction(function () use ($r, $student) {
      $this->access->students($r->user())->lockForUpdate()->findOrFail($student)->delete();
    });
    return back()->with('status', 'Siswa beserta nilai dan publikasinya dihapus.');
  }
  public function importForm(Request $r)
  {
    return view('students.import', ['units' => $this->access->units($r->user())->get()]);
  }
  public function template()
  {
    return Excel::download(new StudentTemplateExport, 'template_import_siswa.xlsx');
  }
  public function import(Request $r)
  {
    $r->validate(['file' => 'required|file|mimes:xlsx|max:5120']);
    DB::transaction(fn() => Excel::import(new StudentsWorkbook($r->user()), $r->file('file')));
    return to_route('students.index')->with('status', 'Import selesai. Seluruh baris valid telah disimpan.');
  }
  public function lookup(Request $r)
  {
    $data = $r->validate(['q' => 'required|string|max:100']);
    $q = $data['q'];
    return $this->access->students($r->user())->with(['unit', 'nilai100'])->where(fn($b) => $b->where('noreg', 'like', "%$q%")->orWhere('nama_siswa', 'like', "%$q%"))->orderBy('noreg')->limit(20)->get()->map(fn($s) => ['id' => $s->id, 'noreg' => $s->noreg, 'nama_siswa' => $s->nama_siswa, 'asal_sekolah' => $s->asal_sekolah, 'kelas_di_go' => $s->kelas_di_go, 'unit' => $s->unit->nama_unit, 'eligible' => $s->nilai100->contains(fn($nilai) => $nilai->status_testimoni === 'SUDAH')]);
  }
}
