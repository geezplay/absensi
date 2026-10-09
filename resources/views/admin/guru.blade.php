@extends('layouts.app')
@section('title', 'Kelola Guru')
@section('heading', 'Kelola Akun Guru')

@section('content')
<div class="grid gap-4 lg:grid-cols-3">
    <div class="card-pad lg:col-span-1">
        <h2 class="font-bold text-slate-900">Buat akun guru</h2>
        <p class="mb-4 mt-1 text-sm text-slate-500">Guru tidak bisa daftar sendiri. Password awal: <code class="font-semibold">password</code>.</p>
        <form method="POST" action="/admin/guru" class="space-y-3">
            @csrf
            <div>
                <label class="label">Nama lengkap + gelar</label>
                <input class="input" name="nama" required placeholder="mis. Dewi Kartika, S.Pd">
            </div>
            <div>
                <label class="label">Email</label>
                <input class="input" type="email" name="email" required placeholder="guru3@sma.id">
            </div>
            <button class="btn btn-primary w-full">Buat Akun</button>
        </form>
    </div>

    <div class="card lg:col-span-2">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Guru</th><th>Email</th><th class="text-center">Kelas</th><th class="text-center">Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($guru as $g)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $g['nama'] }}</td>
                            <td class="text-slate-500">{{ $g['email'] }}</td>
                            <td class="text-center">{{ $g->kelas_count }}</td>
                            <td class="text-center">
                                @if ($g['aktif'])
                                    <span class="badge badge-final">Aktif</span>
                                @else
                                    <span class="badge badge-arsip">Nonaktif</span>
                                @endif
                                @if ($g['must_change_password'])
                                    <span class="badge badge-draft mt-1">Wajib ganti pass</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <form method="POST" action="/admin/guru/{{ $g['id'] }}/reset" data-confirm="Reset password {{ $g['nama'] }} ke password awal?">
                                        @csrf
                                        <button class="btn btn-secondary btn-sm">Reset</button>
                                    </form>
                                    <form method="POST" action="/admin/guru/{{ $g['id'] }}/toggle" data-confirm="{{ $g['aktif'] ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $g['nama'] }}?">
                                        @csrf
                                        <button class="btn {{ $g['aktif'] ? 'btn-danger' : 'btn-primary' }} btn-sm">{{ $g['aktif'] ? 'Nonaktif' : 'Aktifkan' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
