@extends('layouts.app')
@section('title', $kelas ? 'Ubah Kelas' : 'Tambah Kelas')
@section('heading', $kelas ? 'Ubah Kelas' : 'Tambah Kelas')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="card-pad">
        <a href="{{ $kelas ? '/kelas/'.$kelas['id'] : '/kelas' }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800">
            @include('layouts.icon', ['name' => 'arrow-left']) Kembali
        </a>

        <div class="mb-5 rounded-xl bg-brand-50 p-3.5 text-sm text-brand-700 ring-1 ring-brand-100">
            Satu kelas untuk <strong>satu mata pelajaran</strong>. Mengajar 2 mapel di kelas paralel yang sama?
            Buat 2 kelas, mis. <em>XII IPA 1 — Matematika</em> dan <em>XII IPA 1 — Fisika</em>.
        </div>

        <form method="POST" action="{{ $kelas ? '/kelas/'.$kelas['id'] : '/kelas' }}">
            @csrf
            @if ($kelas) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="nama">Nama Kelas</label>
                    <input class="input" id="nama" name="nama" value="{{ old('nama', $kelas['nama'] ?? '') }}" placeholder="mis. XII IPA 1" required>
                </div>
                @php
                    $mapelVal = old('mapel', is_string($kelas['mapel'] ?? null) ? $kelas['mapel'] : ($kelas['mapel'][0] ?? ''));
                    $mapelOptions = ['— pilih satu mapel —' => ''];
                    foreach ($mapelPool as $m) {
                        $mapelOptions[$m] = $m;
                    }
                @endphp
                <div class="sm:col-span-2">
                    <label class="label">Mata Pelajaran</label>
                    <x-select name="mapel" :options="$mapelOptions" :value="$mapelVal" />
                </div>
                <div>
                    <label class="label">Tingkat</label>
                    <x-select name="tingkat" :options="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']" :value="old('tingkat', $kelas['tingkat'] ?? 'XII')" />
                </div>
                <div>
                    <label class="label" for="tahun_ajaran">Tahun Ajaran</label>
                    <input class="input" id="tahun_ajaran" name="tahun_ajaran" value="{{ old('tahun_ajaran', $kelas['tahun_ajaran'] ?? $tahunAjaran) }}" required>
                </div>
                <div>
                    <label class="label" for="wali_kelas">Wali Kelas (opsional)</label>
                    <input class="input" id="wali_kelas" name="wali_kelas" value="{{ old('wali_kelas', $kelas['wali_kelas'] ?? $user['nama']) }}">
                </div>
                <div>
                    <label class="label" for="keterangan">Keterangan (opsional)</label>
                    <input class="input" id="keterangan" name="keterangan" value="{{ old('keterangan', $kelas['keterangan'] ?? '') }}">
                </div>
            </div>

            <button class="btn btn-primary mt-6 w-full py-3">
                {{ $kelas ? 'Simpan Perubahan' : 'Buat Kelas' }}
            </button>
        </form>
    </div>
</div>
@endsection
