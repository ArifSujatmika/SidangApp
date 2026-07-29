<x-layouts::app :title="__('Dashboard Dosen')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Dashboard Dosen</h1>

        @forelse ($schedules as $schedule)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                <h2 class="text-lg font-semibold">{{ $schedule->nama_grup_sidang }}</h2>
                <p class="text-sm text-neutral-500">Tanggal Sidang: {{ $schedule->tanggal_sidang->format('d M Y') }} | {{ $schedule->ruangan }} | {{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}</p>
                <p class="text-xs text-neutral-400">Dosen terpasang: {{ $schedule->dosens->pluck('name')->join(', ') ?: 'Belum ada' }}</p>

                @if ($schedule->submissions->count())
                    <flux:table class="mt-3 w-full">
                        <flux:table.columns>
                            <flux:table.column>Mahasiswa</flux:table.column>
                            <flux:table.column>Judul</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($schedule->submissions as $submission)
                                <flux:table.row>
                                    <flux:table.cell>{{ $submission->user->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $submission->judul_laporan }}</flux:table.cell>
                                    <flux:table.cell>{{ $submission->status }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button variant="ghost" size="xs" :href="route('submissions.show', $submission)" wire:navigate>Detail</flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @else
                    <p class="mt-2 text-sm text-neutral-400">Tidak ada mahasiswa di sesi ini.</p>
                @endif
            </div>
        @empty
            <p class="text-neutral-500">Tidak ada jadwal sidang hari ini.</p>
        @endforelse
    </div>
</x-layouts::app>