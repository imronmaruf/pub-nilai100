@extends('layouts.app')
@section('title','Pengaturan Akun')
@section('content')
<h1 class="mb-5 text-lg font-semibold">{{ $record->exists?'Edit':'Tambah' }} Akun</h1>
<form method="POST" action="{{ $record->exists?route('users.update',$record->id):route('users.store') }}" class="max-w-xl space-y-4">@csrf @if($record->exists)@method('PUT')@endif
<x-field name="name" label="Nama" :value="$record->name" maxlength="255" required/><x-field name="email" label="Email" type="email" :value="$record->email" maxlength="255" required/>
<x-field name="password" label="Password (minimal 12 karakter)" type="password" minlength="12" autocomplete="new-password" :required="!$record->exists"/><x-field name="password_confirmation" label="Ulangi password" type="password" autocomplete="new-password"/>
@if($record->exists)<p class="text-sm text-slate-500">Kosongkan password jika tidak diubah.</p>@endif
<div class="field"><label class="label" for="role">Role</label><select name="role" id="role">@foreach(['Admin Unit','Superadmin'] as $role)<option @selected(old('role',$record->getRoleNames()->first())===$role)>{{ $role }}</option>@endforeach</select></div>
<div class="field"><label class="label" for="unit_id">Unit (wajib untuk Admin Unit)</label><select name="unit_id" id="unit_id"><option value="">Pilih unit</option>@foreach($units as $u)<option value="{{ $u->id }}" @selected(old('unit_id',$record->unit_id)==$u->id)>{{ $u->kota }} — {{ $u->nama_unit }}</option>@endforeach</select></div>
<button class="btn">Simpan</button><a class="btn-secondary" href="{{ route('users.index') }}">Batal</a></form>
@endsection
