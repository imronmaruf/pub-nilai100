@extends('layouts.app')
@section('title', 'Import Publikasi ' . strtoupper($channel))
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Publikasi {{ strtoupper($channel) }}</p>
            <h1>Import Publikasi</h1>
            <p class="muted">Gunakan Noreg siswa yang sudah memiliki Nilai 100 dengan testimoni SUDAH.</p>
        </div><a class="btn-secondary" href="{{ route('publications.import.template', $channel) }}"><i
                class="fa-solid fa-download"></i> Download template</a>
    </div>
    <form method="POST" action="{{ route('publications.import.store', $channel) }}" enctype="multipart/form-data"
        class="filter-panel max-w-2xl">@csrf<div class="field"><label class="label" for="file">File Excel
                (.xlsx)</label><input id="file" type="file" name="file" accept=".xlsx" required></div>
        <div class="mt-4 flex gap-2"><button class="btn"><i class="fa-solid fa-file-import"></i> Import</button><a
                class="btn-secondary" href="{{ route('publications.index', $channel) }}">Batal</a></div>
    </form>
    <div class="stat-card max-w-2xl"><span>Format kolom</span><strong
            class="!mt-1 !text-base">{{ $channel === 'wa' ? 'Noreg, Jumlah Testimoni Terkirim, Tanggal Blast, Terkirim, Dibaca, Respon' : ($channel === 'ig' ? 'Noreg, Status Publikasi, Jumlah Postingan, Tanggal Posting, Link Postingan, View, Like, Komen' : 'Noreg, Jumlah Postingan, Tanggal Posting, Link Postingan, View, Like, Komen') }}</strong>
        <p class="muted">Tanggal menggunakan format DD/MM/YYYY.</p>
    </div>
@endsection
