<x-layouts::app :title="__('Manage Schedules')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Kelola Jadwal Sidang</h1>
            <flux:button variant="primary" :href="route('admin.schedules.create')" wire:navigate>Tambah Jadwal</flux:button>
        </div>

        <flux:table class="min-w-full">
            <flux:table.columns>
                <flux:table.column>Nama Grup</flux:table.column>
                <flux:table.column>Ruangan</flux:table.column>
                <flux:table.column>Tanggal</flux:table.column>
                <flux:table.column>Jam</flux:table.column>
                <flux:table.column>Dosen Penguji</flux:table.column>
                <flux:table.column>Submissions</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($schedules as $schedule)
                    <flux:table.row>
                        <flux:table.cell>{{ $schedule->nama_grup_sidang }}</flux:table.cell>
                        <flux:table.cell>{{ $schedule->ruangan }}</flux:table.cell>
                        <flux:table.cell>{{ $schedule->tanggal_sidang->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}</flux:table.cell>
                        <flux:table.cell>{{ $schedule->dosens->pluck('name')->join(', ') ?: '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $schedule->submissions_count }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button variant="ghost" size="xs" :href="route('admin.schedules.edit', $schedule)" wire:navigate>Edit</flux:button>
                                <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="inline" onsubmit="return confirm('Hapus jadwal {{ $schedule->nama_grup_sidang }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button variant="ghost" size="xs" type="submit" class="!text-red-600">Hapus</flux:button>
                                </form>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>