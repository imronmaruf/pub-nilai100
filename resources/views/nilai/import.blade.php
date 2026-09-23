@extends('layouts.app')
@section('title', 'Import Nilai 100')
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Nilai 100</p>
            <h1>Import Nilai 100</h1>
            <p class="muted">Gunakan Noreg untuk menghubungkan nilai ke siswa.</p>
        </div><a class="btn-secondary" href="{{ route('nilai.import.template') }}"><i class="fa-solid fa-download"></i>
            Download template</a>
    </div>
    <form method="POST" action="{{ route('nilai.import.store') }}" enctype="multipart/form-data"
        class="filter-panel max-w-2xl">@csrf<div class="field"><label class="label" for="file">File Excel
                (.xlsx)</label><input id="file" type="file" name="file" accept=".xlsx" required></div>
        <div class="mt-4 flex gap-2"><button class="btn"><i class="fa-solid fa-file-import"></i> Import</button><a
                class="btn-secondary" href="{{ route('nilai.index') }}">Batal</a></div>
    </form>
    <div class="stat-card max-w-2xl"><span>Format kolom</span><strong class="!mt-1 !text-base">Noreg, Jenis Nilai, Tanggal
            Ujian, Tanggal Validasi PT, Status Testimoni</strong>
        <p class="muted">Tanggal menggunakan format DD/MM/YYYY. Mapel: MAT, FIS, KIM, BIO, GEO, SOS, INFOR, MAT-TL, ING-TL,
            SEJ, IPA, IPS, PKN, EKO, B.INDO, B.ING. Jenis: UH, PTS, PAS, PAT, US.</p>
    </div>
@endsection
