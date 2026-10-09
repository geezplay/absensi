@extends('layouts.app')
@section('title', 'Pratinjau Import')
@section('heading', 'Pratinjau Import · '.$kelas['nama'])

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="/kelas/{{ $kelas['id'] }}/import" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800">
        @include('layouts.icon', ['name' => 'arrow-left']) Unggah file lain
    </a>

    <div class="card-pad mb-4 flex flex-wrap items-center gap-2 text-sm">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-100 text-brand-700">@include('layouts.icon', ['name' => 'file'])</span>
        <span class="font-medium text-slate-700">{{ $namaFile }}</span>
        <span class="text-slate-400">· {{ count($valid) + count($gagal) }} baris dibaca</span>
    </div>

    @if ($dipotong)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            File melebihi 500 baris — hanya 500 baris pertama yang diproses.
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-xl bg-emerald-50 p-4 text-center ring-1 ring-emerald-200">
            <p class="text-3xl font-bold text-emerald-700">{{ count($valid) }}</p>
            <p class="text-xs font-semibold text-emerald-600">Baris valid (akan disimpan)</p>
        </div>
        <div class="rounded-xl bg-rose-50 p-4 text-center ring-1 ring-rose-200">
            <p class="text-3xl font-bold text-rose-700">{{ count($gagal) }}</p>
            <p class="text-xs font-semibold text-rose-600">Baris gagal (ditolak)</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th class="w-16">Baris</th><th>NIS</th><th>Nama</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($valid as $v)
                        <tr class="bg-emerald-50/50">
                            <td class="text-slate-400">{{ $v['baris'] }}</td>
                            <td class="font-mono">{{ $v['nis'] }}</td>
                            <td>{{ $v['nama'] }}</td>
                            <td><span class="badge badge-final">Valid</span></td>
                        </tr>
                    @endforeach
                    @foreach ($gagal as $g)
                        <tr class="bg-rose-50/60">
                            <td class="text-slate-400">{{ $g['baris'] }}</td>
                            <td class="font-mono">{{ $g['nis'] ?: '—' }}</td>
                            <td>{{ $g['nama'] ?: '—' }}</td>
                            <td><span class="badge badge-A">{{ $g['alasan'] }}</span></td>
                        </tr>
                    @endforeach
                    @if (empty($valid) && empty($gagal))
                        <tr><td colspan="4" class="py-10 text-center text-slate-400">File kosong / tidak ada baris data.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="/kelas/{{ $kelas['id'] }}/import/confirm" class="mt-4">
        @csrf
        <input type="hidden" name="stok" value="{{ $stok }}">
        <button class="btn btn-primary w-full py-3" @disabled(count($valid) === 0)>
            Konfirmasi Simpan ({{ count($valid) }} baris valid)
        </button>
        <p class="mt-1.5 text-center text-xs text-slate-400">Hanya baris valid yang disimpan. Baris gagal diabaikan (AC-03).</p>
    </form>
</div>
@endsection
