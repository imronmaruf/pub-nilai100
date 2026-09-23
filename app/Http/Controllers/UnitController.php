<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\{Rule, ValidationException};

class UnitController extends Controller
{
  public function index(Request $r)
  {
    $perPage = in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10;
    return view('admin.units', ['rows' => Unit::orderBy('kota')->paginate($perPage)->withQueryString(), 'record' => new Unit]);
  }
  public function edit(Request $r, string $unit)
  {
    $perPage = in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10;
    return view('admin.units', ['rows' => Unit::orderBy('kota')->paginate($perPage)->withQueryString(), 'record' => Unit::findOrFail($unit)]);
  }
  private function data(Request $r, ?string $id = null)
  {
    return $r->validate(['kota' => 'required|string|max:255', 'nama_unit' => ['required', 'string', 'max:255', Rule::unique('units')->where('kota', $r->input('kota'))->ignore($id)]]);
  }
  public function store(Request $r)
  {
    Unit::create($this->data($r));
    return to_route('units.index')->with('status', 'Unit ditambahkan.');
  }
  public function update(Request $r, string $unit)
  {
    Unit::findOrFail($unit)->update($this->data($r, $unit));
    return to_route('units.index')->with('status', 'Unit diperbarui.');
  }
  public function destroy(string $unit)
  {
    $u = Unit::findOrFail($unit);
    if ($u->students()->exists() || $u->users()->exists()) throw ValidationException::withMessages(['unit' => 'Unit masih memiliki siswa atau akun.']);
    $u->delete();
    return to_route('units.index')->with('status', 'Unit dihapus.');
  }
}
