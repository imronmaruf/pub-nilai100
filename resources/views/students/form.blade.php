@extends('layouts.app')
@section('title', $student->exists ? 'Edit Siswa' : 'Tambah Siswa')
@section('content')
    <h1 class="mb-5 text-lg font-semibold">
        {{ $student->exists ? 'Edit Siswa' : 'Tambah Siswa' }}</h1>
    <form method="POST" action="{{ $student->exists ? route('students.update', $student->id) : route('students.store') }}"
        class="max-w-2xl space-y-4">@csrf @if ($student->exists)
            @method('PUT')
        @endif
        <x-field name="noreg" label="Noreg" :value="$student->noreg" maxlength="50" required /><x-field name="nama_siswa"
            label="Nama siswa" :value="$student->nama_siswa" maxlength="255" required />
        <x-field name="asal_sekolah" label="Asal sekolah" :value="$student->asal_sekolah" maxlength="255" required /><x-field
            name="kelas_di_go" label="Kelas di GO" :value="$student->kelas_di_go" maxlength="100" required />
        <x-field name="tingkat_kelas" label="Tingkat kelas" :value="$student->tingkat_kelas" maxlength="50" required />
        <div class="field"><label for="level" class="label">Level</label><select id="level" name="level"
                required>
                @foreach (['SD', 'SMP', 'SMA'] as $level)
                    <option value="{{ $level }}" @selected(old('level', $student->level) === $level)>{{ $level }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label for="unit_id" class="label">Unit</label><select id="unit_id" name="unit_id" required>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}" @selected(old('unit_id', $student->unit_id) == $u->id)>{{ $u->kota }} —
                        {{ $u->nama_unit }}</option>
                @endforeach
            </select></div>
        <div class="flex gap-2"><button class="btn">Simpan</button><a class="btn-secondary"
                href="{{ route('students.index') }}">Batal</a></div>
    </form>
@endsection
