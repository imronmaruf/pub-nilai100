<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\{Rule, ValidationException};

class MapelController extends Controller
{
  public function index(Request $request)
  {
    $perPage = in_array($request->integer('per_page'), [10, 20, 30], true) ? $request->integer('per_page') : 10;
    return view('admin.mapels', ['rows' => Mapel::orderBy('kode')->paginate($perPage)->withQueryString(), 'record' => new Mapel]);
  }
  public function edit(Request $request, string $mapel)
  {
    $perPage = in_array($request->integer('per_page'), [10, 20, 30], true) ? $request->integer('per_page') : 10;
    return view('admin.mapels', ['rows' => Mapel::orderBy('kode')->paginate($perPage)->withQueryString(), 'record' => Mapel::findOrFail($mapel)]);
  }
  private function data(Request $request, ?string $id = null): array
  {
    return $request->validate(['kode' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9.-]+$/', Rule::unique('mapels', 'kode')->ignore($id)], 'nama' => 'required|string|max:100']);
  }
  public function store(Request $request)
  {
    Mapel::create($this->data($request));
    return to_route('mapels.index')->with('status', 'Mapel ditambahkan.');
  }
  public function update(Request $request, string $mapel)
  {
    $record = Mapel::findOrFail($mapel);
    $data = $this->data($request, $mapel);
    if ($record->kode !== $data['kode'] && $record->nilai100()->exists()) throw ValidationException::withMessages(['kode' => 'Kode mapel yang sudah digunakan tidak dapat diubah.']);
    $record->update($data);
    return to_route('mapels.index')->with('status', 'Mapel diperbarui.');
  }
  public function destroy(string $mapel)
  {
    $record = Mapel::findOrFail($mapel);
    if ($record->nilai100()->exists()) throw ValidationException::withMessages(['mapel' => 'Mapel masih digunakan oleh data Nilai 100.']);
    $record->delete();
    return to_route('mapels.index')->with('status', 'Mapel dihapus.');
  }
}
