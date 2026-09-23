<?php

namespace App\Http\Controllers;

use App\Models\{User, Unit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

class UserController extends Controller
{
  public function index(Request $r)
  {
    $perPage = in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10;
    return view('admin.users', ['rows' => User::with('roles', 'unit')->orderBy('name')->paginate($perPage)->withQueryString()]);
  }
  public function create()
  {
    return view('admin.user-form', ['record' => new User, 'units' => Unit::orderBy('nama_unit')->get()]);
  }
  public function edit(string $user)
  {
    return view('admin.user-form', ['record' => User::with('roles')->findOrFail($user), 'units' => Unit::orderBy('nama_unit')->get()]);
  }
  public function store(Request $r)
  {
    return $this->save($r, null);
  }
  public function update(Request $r, string $user)
  {
    return $this->save($r, $user);
  }
  private function save(Request $r, ?string $id)
  {
    $data = $r->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)], 'password' => [$id ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'], 'role' => ['required', Rule::in(['Superadmin', 'Admin Unit'])], 'unit_id' => ['nullable', 'required_if:role,Admin Unit', 'integer', 'exists:units,id']]);
    if ($id && (int)$id === $r->user()->id && $data['role'] !== 'Superadmin') throw ValidationException::withMessages(['role' => 'Tidak dapat menurunkan role akun sendiri.']);
    DB::transaction(function () use ($id, $data) {
      $u = $id ? User::findOrFail($id) : new User;
      $role = $data['role'];
      unset($data['role']);
      if (empty($data['password'])) unset($data['password']);
      $data['unit_id'] = $role === 'Superadmin' ? null : $data['unit_id'];
      $u->fill($data)->save();
      $u->syncRoles([$role]);
    });
    return to_route('users.index')->with('status', 'Akun disimpan.');
  }
  public function destroy(Request $r, string $user)
  {
    if ((int)$user === $r->user()->id) throw ValidationException::withMessages(['user' => 'Tidak dapat menghapus akun sendiri.']);
    User::findOrFail($user)->delete();
    return back()->with('status', 'Akun dihapus.');
  }
}
