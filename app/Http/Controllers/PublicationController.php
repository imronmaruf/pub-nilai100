<?php

namespace App\Http\Controllers;

use App\Services\{Access, PublicationService};
use App\Http\Requests\PublicationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\PublicationImport;
use App\Exports\ActivityTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class PublicationController extends Controller
{
  public function __construct(private Access $access, private PublicationService $service) {}
  public function index(Request $r, string $channel)
  {
    $perPage = in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10;
    $rows = $this->access->records($this->service->model($channel), $r->user())->with('student.unit')->latest()->paginate($perPage)->withQueryString();
    return view('publications.index', compact('rows', 'channel'));
  }
  public function importForm(string $channel){$this->service->model($channel);return view('publications.import',compact('channel'));}
  public function template(string $channel){$this->service->model($channel);return Excel::download(new ActivityTemplateExport($channel),'template_import_'.$channel.'.xlsx');}
  public function import(Request $r,string $channel){$this->service->model($channel);$r->validate(['file'=>'required|file|mimes:xlsx|max:5120']);DB::transaction(fn()=>Excel::import(new PublicationImport($r->user(),$channel),$r->file('file')));return to_route('publications.index',$channel)->with('status','Import publikasi selesai.');}
  public function create(string $channel)
  {
    $model = $this->service->model($channel);
    return view('publications.form', ['channel' => $channel, 'record' => new $model, 'student' => null]);
  }
  public function edit(Request $r, string $channel, string $publication)
  {
    $record = $this->access->records($this->service->model($channel), $r->user())->with('student.unit', 'student.nilai100')->findOrFail($publication);
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
      $this->service->eligible($s);
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
