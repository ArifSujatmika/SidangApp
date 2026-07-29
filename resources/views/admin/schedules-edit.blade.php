<x-layouts::app :title="__('Edit Jadwal')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Edit Jadwal</h1>

        <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="space-y-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm">Nama Grup Sidang</label>
                <input name="nama_grup_sidang" value="{{ old('nama_grup_sidang', $schedule->nama_grup_sidang) }}" class="w-full rounded border px-3 py-2" required>
            </div>
            <div>
                <label class="mb-1 block text-sm">Ruangan</label>
                <input name="ruangan" value="{{ old('ruangan', $schedule->ruangan) }}" class="w-full rounded border px-3 py-2" required>
            </div>
            <div>
                <label class="mb-1 block text-sm">Tanggal Sidang</label>
                <input name="tanggal_sidang" type="date" value="{{ old('tanggal_sidang', $schedule->tanggal_sidang->format('Y-m-d')) }}" class="w-full rounded border px-3 py-2" required>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm">Jam Mulai</label>
                    <input name="jam_mulai" type="time" value="{{ old('jam_mulai', $schedule->jam_mulai) }}" class="w-full rounded border px-3 py-2" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm">Jam Selesai</label>
                    <input name="jam_selesai" type="time" value="{{ old('jam_selesai', $schedule->jam_selesai) }}" class="w-full rounded border px-3 py-2" required>
                </div>
            </div>
            <div
                x-data="{
                    selected: @js(array_map('strval', old('dosen_ids', $schedule->dosens->pluck('id')->all()))),
                    options: @js($dosens->pluck('id')->map(fn ($id) => (string) $id)->values()),
                    toggleAll() { this.selected = this.selected.length === this.options.length ? [] : [...this.options] },
                }"
            >
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label class="text-sm font-medium">Dosen Penguji</label>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="rounded-full bg-teal-50 px-2.5 py-1 font-medium text-teal-700 dark:bg-teal-950/50 dark:text-teal-300" x-text="`${selected.length} dipilih`"></span>
                        <button type="button" class="font-medium text-teal-600 hover:underline dark:text-teal-400" @click="toggleAll()" x-text="selected.length === options.length && options.length ? 'Hapus semua' : 'Pilih semua'"></button>
                    </div>
                </div>
                <div class="max-h-48 space-y-1 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-neutral-900">
                    @forelse ($dosens as $dosen)
                        <label class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-teal-50 dark:hover:bg-neutral-800">
                            <input x-model="selected" type="checkbox" name="dosen_ids[]" value="{{ $dosen->id }}" class="size-4 rounded border-neutral-300 text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-medium">{{ $dosen->name }}</span>
                        </label>
                    @empty
                        <p class="px-2 py-3 text-center text-sm text-neutral-400">Belum ada data dosen.</p>
                    @endforelse
                </div>
                @error('dosen_ids') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            @php($selectedParticipants = old('peserta_ids', $schedule->submissions->pluck('user_id')->all()))
            <div
                x-data="{
                    query: '',
                    selected: @js(array_map('strval', $selectedParticipants)),
                    options: @js($mahasiswas->pluck('id')->map(fn ($id) => (string) $id)->values()),
                    toggleAll() { this.selected = this.selected.length === this.options.length ? [] : [...this.options] },
                    matches(value) { return value.toLowerCase().includes(this.query.toLowerCase()) },
                }"
            >
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label for="participant-search-edit" class="text-sm font-medium">Mahasiswa Peserta</label>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="rounded-full bg-teal-50 px-2.5 py-1 font-medium text-teal-700 dark:bg-teal-950/50 dark:text-teal-300" x-text="`${selected.length} dipilih`"></span>
                        <button type="button" class="font-medium text-teal-600 hover:underline dark:text-teal-400" @click="toggleAll()" x-text="selected.length === options.length && options.length ? 'Hapus semua' : 'Pilih semua'"></button>
                    </div>
                </div>
                <div class="relative mb-2">
                    <input x-model="query" id="participant-search-edit" type="search" placeholder="Cari nama atau NIM..." class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 dark:border-neutral-700 dark:bg-neutral-900">
                </div>
                <div class="max-h-64 space-y-1 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-neutral-900">
                    @forelse ($mahasiswas as $mahasiswa)
                        <label x-show="matches(@js($mahasiswa->name.' '.$mahasiswa->username))" x-cloak class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-teal-50 dark:hover:bg-neutral-800">
                            <input x-model="selected" type="checkbox" name="peserta_ids[]" value="{{ $mahasiswa->id }}" class="size-4 shrink-0 rounded border-neutral-300 text-teal-600 focus:ring-teal-500">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium">{{ $mahasiswa->name }}</span>
                                <span class="block text-xs text-neutral-500">NIM {{ $mahasiswa->username }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="px-2 py-3 text-center text-sm text-neutral-400">Belum ada data mahasiswa.</p>
                    @endforelse
                </div>
                <p class="mt-1 text-xs text-neutral-400">Peserta yang sudah mengunggah laporan tidak akan dikeluarkan saat pilihan dibatalkan.</p>
                @error('peserta_ids') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white hover:bg-teal-700">Simpan</button>
                <a href="{{ route('admin.schedules.index') }}" class="rounded-lg border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::app>
