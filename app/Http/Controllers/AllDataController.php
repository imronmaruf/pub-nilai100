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
    $units = $this->access->units($request->user())->orderBy('nama_unit')->get();
    $dateFilter = function ($query, string $column) use ($request) {
      return $query->when($request->filled('from'), fn($q) => $q->whereDate($column, '>=', $request->input('from')))->when($request->filled('to'), fn($q) => $q->whereDate($column, '<=', $request->input('to')));
    };
    $rows = $this->access->students($request->user())->with('unit')
      ->when($request->filled('kota'), fn($q) => $q->whereHas('unit', fn($u) => $u->where('kota', $request->input('kota'))))
      ->when($request->filled('unit_id'), fn($q) => $q->where('unit_id', $request->input('unit_id')))
      ->withCount(['nilai100' => fn($q) => $dateFilter($q, 'tanggal_ujian')])
      ->withCount(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')])
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'jumlah_postingan')
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'view')
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'like')
      ->withSum(['instagram' => fn($q) => $dateFilter($q, 'tanggal_posting')->where('status_publikasi', 'SUDAH')], 'komen')
      ->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'jumlah_postingan')->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'view')->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'like')->withSum(['tiktok' => fn($q) => $dateFilter($q, 'tanggal_posting')], 'komen')
      ->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'jumlah_testimoni_terkirim')->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'terkirim')->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'dibaca')->withSum(['whatsapp' => fn($q) => $dateFilter($q, 'tanggal_blast')], 'respon')
      ->orderBy('nama_siswa')->paginate(in_array($request->integer('per_page'), [10, 20, 30], true) ? $request->integer('per_page') : 10)->withQueryString();
    return view('all-data.index', ['rows' => $rows, 'units' => $units, 'cities' => $units->pluck('kota')->unique()->sort()->values()]);
  }
  public function export(Request $request)
  {
    return Excel::download(new ReportExport($request->user()), 'Nilai100_AllData_' . now()->format('Ymd_His') . '.xlsx');
  }
}
