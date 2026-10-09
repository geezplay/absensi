@extends('layouts.app')
@section('title', 'Buat Sesi')
@section('heading', 'Buat Sesi · '.$kelas['nama'])

@section('content')
<div class="mx-auto max-w-xl">
    <div class="card-pad">
        <a href="/kelas/{{ $kelas['id'] }}?tab=sesi" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800">
            @include('layouts.icon', ['name' => 'arrow-left']) Kembali
        </a>

        <form method="POST" action="/kelas/{{ $kelas['id'] }}/sesi" class="space-y-4">
            @csrf
            <div>
                <label class="label">Mata Pelajaran</label>
                <input class="input bg-slate-50" value="{{ $kelas['mapel'] }}" disabled>
                <p class="mt-1 text-xs text-slate-400">Mengikuti mapel kelas ini (1 kelas = 1 mapel).</p>
            </div>
            <div>
                <label class="label" for="tanggal">Tanggal</label>
                <input class="input" id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', $today) }}" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="jam_mulai">Jam Mulai</label>
                    <input class="input" id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai', '07:00') }}" required>
                </div>
                <div>
                    <label class="label" for="jam_selesai">Jam Selesai</label>
                    <input class="input" id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai', '08:30') }}" required>
                </div>
            </div>
            <div>
                <label class="label" for="topik">Topik / Materi (opsional)</label>
                <input class="input" id="topik" name="topik" value="{{ old('topik') }}" maxlength="150" placeholder="mis. Turunan Fungsi">
            </div>
            <div class="rounded-xl bg-slate-50 p-3.5 text-xs text-slate-500">
                Kombinasi tanggal + jam mulai harus unik per kelas. Duplikat akan ditolak.
            </div>
            <button class="btn btn-primary w-full py-3">Buat Sesi & Isi Absensi</button>
        </form>
    </div>
</div>
@endsection
