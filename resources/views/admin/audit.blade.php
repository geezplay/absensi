@extends('layouts.app')
@section('title', 'Audit Log Global')
@section('heading', 'Audit Log Global')

@section('content')
<p class="mb-4 text-sm text-slate-500">Append-only: tidak bisa diubah/dihapus siapa pun termasuk super admin.</p>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Waktu</th><th>Pelaku</th><th>Aksi</th><th>Sesi</th><th>Alasan</th></tr></thead>
            <tbody>
                @foreach ($logs as $log)
                    @php $s = $log->sesi; @endphp
                    <tr>
                        <td class="whitespace-nowrap text-slate-500">{{ $log->waktu instanceof \DateTimeInterface ? $log->waktu->format('Y-m-d H:i') : $log->waktu }}</td>
                        <td class="font-medium">{{ $log->actor->nama ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $log->aksi }}</span></td>
                        <td class="text-slate-500">{{ $s ? $s->mapel.' · '.date('d M Y', strtotime($s->tanggal)) : '—' }}</td>
                        <td class="max-w-xs truncate text-slate-500">{{ $log->alasan ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
