@php
    $selected = $student ?? null;
    if (!$selected && old('student_id')) {
        $selected = app(\App\Services\Access::class)
            ->students(auth()->user())
            ->with('unit', 'nilai100.mapelInfo')
            ->find(old('student_id'));
    }
@endphp
<div data-student-picker data-url="{{ route('students.lookup', absolute: false) }}"
    data-eligible="{{ $selected?->nilai100?->contains(fn($nilai) => $nilai->status_testimoni === 'SUDAH') ? '1' : '0' }}"
    class="space-y-4">
    @if (!$student)
        <div class="field"><label class="label" for="student_search">Cari noreg / nama siswa</label><input
                id="student_search" type="search" autocomplete="off" maxlength="100"
                placeholder="Ketik minimal 2 karakter">
            <p data-search-status class="text-sm text-slate-500" role="status"></p>
        </div>
        <div class="field"><label class="label" for="student_id">Pilih siswa</label><select name="student_id"
                id="student_id" required>
                <option value="">Pilih hasil pencarian</option>
                @if ($selected)
                    <option value="{{ $selected->id }}" selected>{{ $selected->noreg }} — {{ $selected->nama_siswa }}
                    </option>
                @endif
            </select></div>
    @else
        <div class="field"><label class="label" for="selected_noreg">Noreg</label><input id="selected_noreg"
                value="{{ $student->noreg }}" readonly></div>
    @endif
    @if ($showNilai100 ?? false)
        <div class="field">
            <label class="label" for="nilai100_id">Nilai 100 yang dipublikasikan</label>
            <select id="nilai100_id" name="nilai100_id" data-nilai100-select @required(!$record->exists || $record->nilai100_id !== null)
                @disabled(!$selected || !$selected->nilai100->contains(fn($nilai) => $nilai->status_testimoni === 'SUDAH'))>
                @if (!$selected)
                    <option value="">Pilih siswa terlebih dahulu</option>
                @elseif (($record->nilai100_id ?? null) === null)
                    <option value="">
                        {{ $record->exists ?? false ? 'Data lama belum ditautkan ke Nilai 100' : 'Pilih Nilai 100' }}
                    </option>
                @else
                    <option value="">Pilih Nilai 100</option>
                @endif
                @foreach ($selected?->nilai100?->where('status_testimoni', 'SUDAH') ?? [] as $grade)
                    <option value="{{ $grade->id }}" @selected((string) old('nilai100_id', $record->nilai100_id ?? null) === (string) $grade->id)>
                        {{ $grade->mapelInfo?->nama ?? $grade->mapel }} · {{ $grade->jenis_nilai }} ·
                        {{ $grade->tanggal_ujian?->format('d/m/Y') ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach (['nama_siswa' => 'Nama', 'asal_sekolah' => 'Sekolah', 'kelas_di_go' => 'Kelas', 'unit' => 'Unit'] as $field => $label)
            <div class="field"><label class="label"
                    for="display_{{ $field }}">{{ $label }}</label><input
                    id="display_{{ $field }}" data-display="{{ $field }}" readonly
                    value="{{ $field === 'unit' ? $selected?->unit?->nama_unit : $selected?->$field }}"></div>
        @endforeach
    </div>
</div>
