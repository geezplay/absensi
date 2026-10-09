@extends('layouts.app')
@section('title', $sesi['mapel'].' · '.$kelas['nama'])
@section('heading', $sesi['mapel'])

@php
    $isFinal = $sesi['status'] === 'final';
    $opsi = \App\Support\Data::STATUS;
    $nilaiAwal = [];
    foreach ($siswa as $sw) { $nilaiAwal[$sw['id']] = $absensi[$sw['id']] ?? 'H'; }
@endphp

@section('content')
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
    <a href="/kelas/{{ $kelas['id'] }}?tab=sesi" class="inline-flex items-center gap-1.5 font-medium text-slate-500 hover:text-slate-800">
        @include('layouts.icon', ['name' => 'arrow-left']) {{ $kelas['nama'] }}
    </a>
    <span class="text-slate-300">·</span>
    <span class="text-slate-500">{{ date('d M Y', strtotime($sesi['tanggal'])) }} · {{ $sesi['jam_mulai'] }}–{{ $sesi['jam_selesai'] }}</span>
    <span class="badge {{ $isFinal ? 'badge-final' : 'badge-draft' }}">{{ $isFinal ? 'Final · Terkunci' : 'Draft · Buka' }}</span>
    @if ($sesi['topik'])<span class="text-slate-400">· {{ $sesi['topik'] }}</span>@endif
</div>

<div x-data="absensiForm(@js($nilaiAwal), {{ $sesi['id'] }}, {{ $isFinal ? 'true' : 'false' }})">
    <div class="card-pad mb-4 lg:sticky lg:top-20">
        <div class="flex flex-wrap items-center gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800"><span x-text="terhitung"></span> dari {{ count($siswa) }} siswa sudah ditandai</p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-brand-500 transition-all" :style="'width:' + persen + '%'"></div>
                </div>
            </div>
            @if (! $isFinal)
                <button type="button" @click="semuaHadir()" class="btn btn-secondary btn-sm">Tandai semua Hadir</button>
                <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-500">
                    <input type="checkbox" x-model="hanyaBelum" class="h-4 w-4 rounded border-slate-300 text-brand-600"> Belum ditandai
                </label>
            @endif
            <input x-model="cari" @input="hanyaBelum = false" class="input sm:!w-52" placeholder="Cari siswa…">
        </div>
        <div class="mt-3 hidden flex-wrap gap-2 text-xs sm:flex">
            <template x-for="(v, k) in ringkas" :key="k">
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-semibold">
                    <span x-text="k"></span>: <span x-text="v"></span>
                </span>
            </template>
        </div>
    </div>

    <form method="POST"
          :action="aksi === 'final' ? '/sesi/{{ $sesi['id'] }}/finalisasi' : '/sesi/{{ $sesi['id'] }}/draft'"
          @submit="sebelumKirim($event)">
        @csrf
        <input type="hidden" name="_method" :value="aksi === 'draft' ? 'PUT' : 'POST'">

        <div class="space-y-2.5">
            <template x-for="sw in tampil" :key="sw.id">
                <div class="card flex flex-col gap-2.5 p-3.5 sm:flex-row sm:items-center sm:p-4"
                     :class="nilai[sw.id] === 'A' ? 'border-rose-200 bg-rose-50/40' : ''"
                     x-show="!hanyaBelum || nilai[sw.id] !== 'H'">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-bold text-slate-600" x-text="sw.nama.charAt(0).toUpperCase()"></span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800" x-text="sw.nama"></p>
                            <p class="font-mono text-xs text-slate-400" x-text="'NIS ' + sw.nis"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 sm:flex" role="radiogroup">
                        <template x-for="(label, kode) in @js($opsi)" :key="kode">
                            <label class="cursor-pointer">
                                <input type="radio" class="peer sr-only" :name="'status[' + sw.id + ']'" :value="kode"
                                       x-model="nilai[sw.id]" :disabled="terkunci">
                                <span class="block rounded-lg px-3 py-2 text-center text-xs font-bold ring-1 transition"
                                      :class="nilai[sw.id] === kode
                                        ? (kode === 'H' ? 'bg-emerald-600 text-white ring-emerald-600'
                                          : kode === 'I' ? 'bg-amber-500 text-white ring-amber-500'
                                          : kode === 'S' ? 'bg-sky-600 text-white ring-sky-600'
                                          : 'bg-rose-600 text-white ring-rose-600')
                                        : 'bg-white text-slate-500 ring-slate-200 hover:ring-slate-300'"
                                      x-text="label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        @if (! $isFinal)
            <div class="sticky bottom-16 z-20 mt-4 lg:bottom-4">
                <div class="card flex flex-col gap-2 !border-brand-200 p-3 shadow-lg sm:flex-row">
                    <button type="submit" @click="aksi = 'draft'" class="btn btn-secondary flex-1 py-3">Simpan Draft</button>
                    <button type="submit" @click="aksi = 'final'" class="btn btn-primary flex-1 py-3"
                            data-confirm="Finalisasi sesi? Form absensi akan terkunci dan tidak bisa diubah tanpa buka ulang.">Finalisasi · Kunci</button>
                </div>
            </div>
        @endif
    </form>

    @if ($isFinal)
        <div class="card-pad mt-4 border-emerald-200 bg-emerald-50/60">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-600 text-white">@include('layouts.icon', ['name' => 'lock'])</span>
                <div class="flex-1">
                    <p class="font-semibold text-emerald-900">Sesi terkunci</p>
                    <p class="text-sm text-emerald-700/70">Untuk koreksi, buka ulang dengan alasan (min. 10 karakter).</p>
                </div>
                <a href="/sesi/{{ $sesi['id'] }}/audit" class="btn btn-secondary btn-sm">Audit</a>
            </div>
            <form method="POST" action="/sesi/{{ $sesi['id'] }}/reopen" class="mt-3 flex flex-col gap-2 sm:flex-row">
                @csrf
                <input class="input flex-1" name="alasan" minlength="10" required placeholder="Alasan buka ulang (min. 10 karakter)…">
                <button class="btn btn-primary btn-sm shrink-0">Buka Ulang</button>
            </form>
        </div>
    @else
        <form method="POST" action="/sesi/{{ $sesi['id'] }}" data-confirm="Hapus sesi draft ini beserta absensinya?" class="mt-4">
            @csrf @method('DELETE')
            <button class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50">Hapus sesi draft</button>
        </form>
    @endif
</div>
@endsection

@section('scripts')
<script>
function absensiForm(awal, sesiId, terkunci) {
    return {
        nilai: { ...awal },
        siswaSemua: @js($siswa->values()->map(fn ($s) => ['id' => $s->id, 'nis' => $s->nis, 'nama' => $s->nama])->all()),
        aksi: 'draft', cari: '', hanyaBelum: false, terkunci,
        get tampil() {
            const q = this.cari.toLowerCase();
            if (!q) return this.siswaSemua;
            return this.siswaSemua.filter(s => (s.nama + s.nis).toLowerCase().includes(q));
        },
        get terhitung() {
            return Object.values(this.nilai).filter(v => ['H','I','S','A'].includes(v)).length;
        },
        get persen() {
            return Math.round(this.terhitung / this.siswaSemua.length * 100);
        },
        get ringkas() {
            const r = { Hadir: 0, Izin: 0, Sakit: 0, Alfa: 0 };
            const map = { H: 'Hadir', I: 'Izin', S: 'Sakit', A: 'Alfa' };
            Object.values(this.nilai).forEach(v => { if (map[v]) r[map[v]]++; });
            return r;
        },
        semuaHadir() {
            this.siswaSemua.forEach(s => this.nilai[s.id] = 'H');
            this.simpanLokal();
        },
        kunciLokal() { return 'absensi_draft_' + sesiId; },
        simpanLokal() {
            if (this.terkunci) return;
            try { localStorage.setItem(this.kunciLokal(), JSON.stringify(this.nilai)); } catch (e) {}
        },
        sebelumKirim(e) {
            if (this.terkunci) { e.preventDefault(); return; }
            const belum = this.siswaSemua.filter(s => !['H','I','S','A'].includes(this.nilai[s.id]));
            if (this.aksi === 'final' && belum.length) {
                e.preventDefault();
                alert(belum.length + ' siswa belum ditandai. Contoh: ' + belum.slice(0, 3).map(s => s.nama).join(', '));
                return;
            }
            try { localStorage.removeItem(this.kunciLokal()); } catch (err) {}
        },
        init() {
            if (!this.terkunci) {
                try {
                    const d = JSON.parse(localStorage.getItem(this.kunciLokal()) || 'null');
                    if (d) {
                        this.siswaSemua.forEach(s => { if (d[s.id]) this.nilai[s.id] = d[s.id]; });
                    }
                } catch (e) {}
                this.$watch('nilai', () => this.simpanLokal(), { deep: true });
            }
        }
    };
}
</script>
@endsection
