<x-layouts::app :title="__('Tambah Jadwal')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Tambah Jadwal</h1>

        <form method="POST" action="{{ route('admin.schedules.store') }}" class="grid gap-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            @csrf

            <flux:field>
                <flux:label>Nama Grup Sidang</flux:label>
                <input name="nama_grup_sidang" class="w-full rounded border px-3 py-2" required />
                <flux:error name="nama_grup_sidang" />
            </flux:field>

            <flux:field>
                <flux:label>Ruangan</flux:label>
                <input name="ruangan" class="w-full rounded border px-3 py-2" required />
                <flux:error name="ruangan" />
            </flux:field>

            <flux:field>
                <flux:label>Tanggal Sidang</flux:label>
                <input name="tanggal_sidang" type="date" class="w-full rounded border px-3 py-2" required />
                <flux:error name="tanggal_sidang" />
            </flux:field>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:field>
                    <flux:label>Jam Mulai</flux:label>
                    <input name="jam_mulai" type="time" class="w-full rounded border px-3 py-2" required />
                    <flux:error name="jam_mulai" />
                </flux:field>

                <flux:field>
                    <flux:label>Jam Selesai</flux:label>
                    <input name="jam_selesai" type="time" class="w-full rounded border px-3 py-2" required />
                    <flux:error name="jam_selesai" />
                </flux:field>
            </div>

            <flux:field>
                <div
                    x-data="{
                        selected: @js(array_map('strval', old('dosen_ids', []))),
                        options: @js($dosens->pluck('id')->map(fn ($id) => (string) $id)->values()),
                        toggleAll() { this.selected = this.selected.length === this.options.length ? [] : [...this.options] },
                    }"
                >
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <flux:label>Dosen Penguji</flux:label>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="rounded-full bg-yellow-50 px-2.5 py-1 font-medium text-yellow-700 dark:bg-yellow-950/50 dark:text-yellow-300" x-text="`${selected.length} dipilih`"></span>
                            <button type="button" class="font-medium text-yellow-600 hover:underline dark:text-yellow-400" @click="toggleAll()" x-text="selected.length === options.length && options.length ? 'Hapus semua' : 'Pilih semua'"></button>
                        </div>
                    </div>
                    <div class="max-h-48 space-y-1 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-neutral-900">
                        @forelse ($dosens as $dosen)
                            <label class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-yellow-50 dark:hover:bg-neutral-800">
                                <input x-model="selected" type="checkbox" name="dosen_ids[]" value="{{ $dosen->id }}" class="size-4 rounded border-neutral-300 text-yellow-600 focus:ring-yellow-500">
                                <span class="text-sm font-medium">{{ $dosen->name }}</span>
                            </label>
                        @empty
                            <p class="px-2 py-3 text-center text-sm text-neutral-400">Belum ada data dosen.</p>
                        @endforelse
                    </div>
                </div>
                <flux:error name="dosen_ids" />
            </flux:field>

            <flux:field>
                <div
                    x-data="{
                        query: '',
                        selected: @js(array_map('strval', old('peserta_ids', []))),
                        options: @js($mahasiswas->pluck('id')->map(fn ($id) => (string) $id)->values()),
                        toggleAll() { this.selected = this.selected.length === this.options.length ? [] : [...this.options] },
                        matches(value) { return value.toLowerCase().includes(this.query.toLowerCase()) },
                    }"
                >
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <flux:label>Mahasiswa Peserta</flux:label>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="rounded-full bg-yellow-50 px-2.5 py-1 font-medium text-yellow-700 dark:bg-yellow-950/50 dark:text-yellow-300" x-text="`${selected.length} dipilih`"></span>
                            <button type="button" class="font-medium text-yellow-600 hover:underline dark:text-yellow-400" @click="toggleAll()" x-text="selected.length === options.length && options.length ? 'Hapus semua' : 'Pilih semua'"></button>
                        </div>
                    </div>
                    <div class="relative mb-2">
                        <input x-model="query" id="participant-search-create" type="search" placeholder="Cari nama atau NIM..." class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 dark:border-neutral-700 dark:bg-neutral-900">
                    </div>
                    <div class="max-h-64 space-y-1 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-neutral-900">
                        @forelse ($mahasiswas as $mahasiswa)
                            <label x-show="matches(@js($mahasiswa->name.' '.$mahasiswa->username))" x-cloak class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-yellow-50 dark:hover:bg-neutral-800">
                                <input x-model="selected" type="checkbox" name="peserta_ids[]" value="{{ $mahasiswa->id }}" class="size-4 shrink-0 rounded border-neutral-300 text-yellow-600 focus:ring-yellow-500">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium">{{ $mahasiswa->name }}</span>
                                    <span class="block text-xs text-neutral-500">NIM {{ $mahasiswa->username }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="px-2 py-3 text-center text-sm text-neutral-400">Belum ada data mahasiswa.</p>
                        @endforelse
                    </div>
                    <p class="mt-1 text-xs text-neutral-400">Peserta terpilih mendapat submission pending untuk dilengkapi.</p>
                </div>
                <flux:error name="peserta_ids" />
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Simpan</flux:button>
                <a href="{{ route('admin.schedules.index') }}" class="rounded-lg border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::app>