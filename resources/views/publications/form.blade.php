@extends('layouts.app')
@section('title', 'Input Publikasi')
@section('content')
    <h1 class="mb-5 text-lg font-semibold">
        {{ $record->exists ? 'Edit' : 'Tambah' }} Publikasi
        {{ ['ig' => 'Instagram', 'tiktok' => 'TikTok', 'wa' => 'WhatsApp'][$channel] }}</h1>
    <form method="POST" data-publication-form
        action="{{ $record->exists ? route('publications.update', [$channel, $record->id]) : route('publications.store', $channel) }}"
        class="max-w-2xl space-y-5">@csrf @if ($record->exists)
            @method('PUT')
        @endif
        @include('partials.student-picker')
        <p data-eligibility-message role="status" class="text-sm text-slate-600">Pilih siswa dengan Nilai 100 dan testimoni
            SUDAH untuk mengisi publikasi.</p>
        <fieldset data-publication-fields disabled class="space-y-4">
            <legend class="sr-only">Data publikasi</legend>
            @if ($channel === 'wa')
                <x-field name="tanggal_blast" label="Tanggal blast" type="date" :value="$record->tanggal_blast?->format('Y-m-d')" :max="today()->format('Y-m-d')"
                    required />
                @foreach (['jumlah_testimoni_terkirim' => 'Jumlah testimoni terkirim', 'terkirim' => 'Terkirim', 'dibaca' => 'Dibaca', 'respon' => 'Respon'] as $f => $label)
                    <x-field :name="$f" :label="$label" type="number" :min="$f === 'jumlah_testimoni_terkirim' ? 1 : 0" :max="$f === 'jumlah_testimoni_terkirim' ? 1000000 : 1000000000"
                        :value="$record->$f ?? ($f === 'jumlah_testimoni_terkirim' ? 1 : 0)" required />
                @endforeach
            @else
                @if ($channel === 'ig')
                    <div class="field"><label class="label" for="status_publikasi">Status publikasi</label><select
                            name="status_publikasi" id="status_publikasi" data-ig-status>
                            @foreach (['BELUM', 'SUDAH'] as $v)
                                <option @selected(old('status_publikasi', $record->status_publikasi ?? 'BELUM') === $v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <x-field name="jumlah_postingan" label="Jumlah postingan" type="number" min="1" max="1000000"
                    :value="$record->jumlah_postingan ?? 1" required />
                <x-field name="tanggal_posting" label="Tanggal posting" type="date" :value="$record->tanggal_posting?->format('Y-m-d')" :max="today()->format('Y-m-d')"
                    required />
                <x-field name="link_postingan" label="Link postingan" type="url" :value="$record->link_postingan" maxlength="2048"
                    required />
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach (['view' => 'View', 'like' => 'Like', 'komen' => 'Komen'] as $f => $label)
                        <x-field :name="$f" :label="$label" type="number" min="0" max="1000000000"
                            :value="$record->$f ?? 0" required />
                    @endforeach
                </div>
            @endif
            <button class="btn">Simpan</button>
        </fieldset><a class="btn-secondary" href="{{ route('publications.index', $channel) }}">Batal</a>
    </form>
@endsection
