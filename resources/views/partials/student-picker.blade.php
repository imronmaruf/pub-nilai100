@php
    $selected = $student ?? null;
    if (!$selected && old('student_id')) {
        $selected = app(\App\Services\Access::class)
            ->students(auth()->user())
            ->with('unit', 'nilai100')
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
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach (['nama_siswa' => 'Nama', 'asal_sekolah' => 'Sekolah', 'kelas_di_go' => 'Kelas', 'unit' => 'Unit'] as $field => $label)
            <div class="field"><label class="label"
                    for="display_{{ $field }}">{{ $label }}</label><input id="display_{{ $field }}"
                    data-display="{{ $field }}" readonly
                    value="{{ $field === 'unit' ? $selected?->unit?->nama_unit : $selected?->$field }}"></div>
        @endforeach
    </div>
</div>
