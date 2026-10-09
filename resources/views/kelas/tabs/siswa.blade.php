<div class="mb-4 flex flex-wrap items-center gap-2">
    <form method="GET" action="/kelas/{{ $kelas['id'] }}/siswa" class="relative min-w-0 flex-1 sm:max-w-xs">
        <input type="hidden" name="tab" value="siswa">
        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">@include('layouts.icon', ['name' => 'search'])</span>
        <input class="input pl-10" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari nama atau NIS…">
    </form>
    <div class="ms-auto flex flex-wrap gap-2">
        <a href="/kelas/{{ $kelas['id'] }}/template" class="btn btn-secondary btn-sm">@include('layouts.icon', ['name' => 'download']) Template</a>
        <a href="/kelas/{{ $kelas['id'] }}/import" class="btn btn-secondary btn-sm">@include('layouts.icon', ['name' => 'upload']) Import .xlsx</a>
        <button x-data @click="$dispatch('open-tambah')" class="btn btn-primary btn-sm">@include('layouts.icon', ['name' => 'plus']) Tambah</button>
    </div>
</div>

<div x-data="{ tambah: false, edit: null }"
     @open-tambah.window="tambah = true; edit = null"
     @open-edit.window="tambah = false; edit = $event.detail">
    <div x-cloak x-show="tambah" class="card-pad mb-4">
        <p class="mb-3 font-semibold text-slate-900">Tambah siswa manual</p>
        <form method="POST" action="/kelas/{{ $kelas['id'] }}/siswa" class="grid gap-3 sm:grid-cols-[140px_1fr_auto]">
            @csrf
            <input class="input" name="nis" placeholder="NIS" required maxlength="20">
            <input class="input" name="nama" placeholder="Nama lengkap" required maxlength="100">
            <div class="flex gap-2">
                <button class="btn btn-primary btn-sm">Simpan</button>
                <button type="button" @click="tambah = false" class="btn btn-ghost btn-sm">Batal</button>
            </div>
        </form>
    </div>

    <div x-cloak x-show="edit" class="card-pad mb-4">
        <p class="mb-3 font-semibold text-slate-900">Ubah siswa</p>
        <form method="POST" :action="'/siswa/' + edit.id" class="grid gap-3 sm:grid-cols-[140px_1fr_auto]">
            @csrf @method('PUT')
            <input class="input" name="nis" x-model="edit.nis" required maxlength="20">
            <input class="input" name="nama" x-model="edit.nama" required maxlength="100">
            <div class="flex gap-2">
                <button class="btn btn-primary btn-sm">Simpan</button>
                <button type="button" @click="edit = null" class="btn btn-ghost btn-sm">Batal</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th class="w-10">No</th><th>NIS</th><th>Nama Lengkap</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($siswa as $i => $s)
                        <tr>
                            <td class="text-slate-400">{{ $i + 1 }}</td>
                            <td class="font-mono font-medium">{{ $s['nis'] }}</td>
                            <td class="font-medium text-slate-800">{{ $s['nama'] }}</td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <button @click="$dispatch('open-edit', @js($s))" class="btn btn-ghost btn-sm" title="Ubah">@include('layouts.icon', ['name' => 'pencil'])</button>
                                    <form method="POST" action="/siswa/{{ $s['id'] }}" data-confirm="{{ $s['nama'] }} akan dihapus dari kelas ini. Lanjutkan?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm text-rose-600 hover:bg-rose-50" title="Hapus">@include('layouts.icon', ['name' => 'trash'])</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-slate-400">Belum ada siswa. Import atau tambah manual.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
