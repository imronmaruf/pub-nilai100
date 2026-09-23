@extends('layouts.app')
@section('title', $nilai->exists ? 'Edit Nilai' : 'Input Nilai')
@section('content')
    <h1 class="mb-5 text-lg font-semibold">
        {{ $nilai->exists ? 'Edit Nilai' : 'Input Nilai 100' }}</h1>
    <form method="POST" action="{{ $nilai->exists ? route('nilai.update', $student->id) : route('nilai.store') }}"
        class="max-w-2xl space-y-5">@csrf @if ($nilai->exists)
            @method('PUT')
        @endif
        @include('partials.student-picker')
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="field"><label for="mapel" class="label">Mata pelajaran</label><select id="mapel" name="mapel"
                    required>
                    @foreach (\App\Models\Nilai100::MAPEL as $v)
                        <option value="{{ $v }}" @selected(old('mapel', $nilai->mapel) === $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="jenis_nilai" class="label">Jenis nilai 100</label><select id="jenis_nilai"
                    name="jenis_nilai" required>
                    @foreach (['UH', 'PTS', 'PAS', 'PAT', 'US'] as $v)
                        <option @selected(old('jenis_nilai', $nilai->jenis_nilai) === $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label for="status_testimoni" class="label">Status testimoni</label><select
                    id="status_testimoni" name="status_testimoni">
                    @foreach (['BELUM', 'SUDAH'] as $v)
                        <option @selected(old('status_testimoni', $nilai->status_testimoni) === $v)>{{ $v }}</option>
                    @endforeach
                </select></div>
            <x-field name="tanggal_ujian" label="Tanggal ujian" type="date" :value="$nilai->tanggal_ujian?->format('Y-m-d')" :max="today()->format('Y-m-d')"
                required />
            <x-field name="tanggal_validasi_pt" label="Tanggal validasi PT" type="date" :value="$nilai->tanggal_validasi_pt?->format('Y-m-d')"
                :max="today()->format('Y-m-d')" required />
        </div><button class="btn">Simpan</button><a class="btn-secondary" href="{{ route('nilai.index') }}">Batal</a>
    </form>
@endsection
