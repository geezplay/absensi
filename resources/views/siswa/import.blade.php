@extends('layouts.app')
@section('title', 'Import Siswa')
@section('heading', 'Import Siswa · '.$kelas['nama'])

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="card-pad mb-4">
        <p class="font-semibold text-slate-900">Format file (PRD §5.3)</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-500">
            <li>Unggah file Excel <strong>.xlsx</strong> (boleh juga .csv/.ods), maksimal 2 MB.</li>
            <li><strong>Kolom A = NIS, Kolom B = Nama Lengkap.</strong> Baris 1 adalah header — jangan dihapus.</li>
            <li>NIS unik per kelas, nama wajib isi, maksimal 500 baris per file.</li>
        </ul>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="/kelas/{{ $kelas['id'] }}/template" class="btn btn-secondary btn-sm">@include('layouts.icon', ['name' => 'download']) Unduh Template .xlsx</a>
        </div>
    </div>

    <div class="card-pad">
        <form method="POST" action="/kelas/{{ $kelas['id'] }}/import/preview" enctype="multipart/form-data">
            @csrf
            <label class="label" for="file">Pilih file dari perangkat</label>
            <input class="input cursor-pointer file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700"
                   id="file" name="file" type="file" accept=".xlsx,.csv,.ods" required>
            <p class="mt-1.5 text-xs text-slate-400">Contoh: unduh template → isi kolom A (NIS) dan B (Nama) → simpan → unggah di sini.</p>
            <button class="btn btn-primary mt-4 w-full py-3">Unggah & Pratinjau</button>
        </form>
    </div>
</div>
@endsection
