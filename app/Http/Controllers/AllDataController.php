<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Services\Access;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AllDataController extends Controller
{
  public function __construct(private Access $access) {}
  public function index(Request $request)
  {
    foreach (['kota', 'unit_id', 'asal_sekolah', 'kelas_di_go', 'tingkat_kelas', 'level', 'status_testimoni'] as $key) if ($request->filled($key) && !is_array($request->input($key))) $request->merge([$key => [$request->input($key)]]);
    $units = $this->access->units($request->user())->orderBy('nama_unit')->get();
    $dateFilter = function ($query, string $column) use ($request) {
      return $query->when($request->filled('from'), fn($q) => $q->whereDate($column, '>=', $request->input('from')))->when($request->filled('to'), fn($q) => $q->whereDate($column, '<=', $request->input('to')));
    };
    $query = $this->access->students($request->user())->with('unit')
      ->when($request->filled('kota'), fn($q) => $q->whereHas('unit', fn($u) => $u->whereIn('kota', $request->input('kota'))))
      ->when($request->filled('unit_id'), fn($q) => $q->whereIn('unit_id', $request->input('unit_id')))
      ->when($request->filled('asal_sekolah'), fn($q) => $q->whereIn('asal_sekolah', $request->input('asal_sekolah')))
      ->when($request->filled('kelas_di_go'), fn($q) => $q->whereIn('kelas_di_go', $request->input('kelas_di_go')))
      ->when($request->filled('tingkat_kelas'), fn($q) => $q->whereIn('tingkat_kelas', $request->input('tingkat_kelas')))
      ->when($request->filled('level'), fn($q) => $q->whereIn('level', $request->input('level')))
      ->when($request->filled('status_testimoni'), fn($q) => $q->whereHas('nilai100', fn($n) => $n->whereIn('status_testimoni', $request->input('status_testimoni'))))
      ->withCount(['nilai100' => fn($q) => $dateFilter($q, 'tanggal_ujian')])
      ->withCount(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')])
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'jumlah_postingan')
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'view')
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'like')
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'komen')
      ->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'jumlah_postingan')->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'view')->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'like')->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'komen')
      ->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'jumlah_testimoni_terkirim')->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'terkirim')->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'dibaca')->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'respon')
      ->orderBy('nama_siswa');
    $all = (clone $query)->get();
    $totals = ['students' => $all->count(), 'nilai100' => $all->sum('nilai100_count'), 'ig_post' => $all->sum('instagram_sum_jumlah_postingan'), 'ig_view' => $all->sum('instagram_sum_view'), 'ig_like' => $all->sum('instagram_sum_like'), 'ig_komen' => $all->sum('instagram_sum_komen'), 'tt_post' => $all->sum('tiktok_sum_jumlah_postingan'), 'tt_view' => $all->sum('tiktok_sum_view'), 'tt_like' => $all->sum('tiktok_sum_like'), 'tt_komen' => $all->sum('tiktok_sum_komen'), 'wa_kirim' => $all->sum('whatsapp_sum_jumlah_testimoni_terkirim'), 'wa_terkirim' => $all->sum('whatsapp_sum_terkirim'), 'wa_dibaca' => $all->sum('whatsapp_sum_dibaca'), 'wa_respon' => $all->sum('whatsapp_sum_respon')];
    $rows = $query->paginate(in_array($request->integer('per_page'), [10, 20, 30], true) ? $request->integer('per_page') : 10)->withQueryString();
    $options = $this->access->students($request->user())->get(['asal_sekolah', 'kelas_di_go', 'tingkat_kelas', 'level']);
    return view('all-data.index', ['rows' => $rows, 'totals' => $totals, 'units' => $units, 'cities' => $units->pluck('kota')->unique()->sort()->values(), 'schools' => $options->pluck('asal_sekolah')->unique()->sort()->values(), 'goClasses' => $options->pluck('kelas_di_go')->unique()->sort()->values(), 'grades' => $options->pluck('tingkat_kelas')->unique()->sort()->values()]);
  }
  public function export(Request $request)
  {
    return Excel::download(new ReportExport($request->user()), 'Nilai100_AllData_' . now()->format('Ymd_His') . '.xlsx');
  }
}
