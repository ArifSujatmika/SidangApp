<x-layouts::app :title="__('Dashboard Dosen')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Dashboard Dosen</h1>

        @forelse ($schedules as $schedule)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                <h2 class="text-lg font-semibold">{{ $schedule->nama_grup_sidang }}</h2>
                <p class="text-sm text-neutral-500">Tanggal Sidang: {{ $schedule->tanggal_sidang->format('d M Y') }} | {{ $schedule->ruangan }} | {{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}</p>
                <p class="text-xs text-neutral-400">Dosen terpasang: {{ $schedule->dosens->pluck('name')->join(', ') ?: 'Belum ada' }}</p>

                @if ($schedule->submissions->count())
                    <table class="mt-3 w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="py-2">Mahasiswa</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedule->submissions as $submission)
                                <tr class="border-b">
                                    <td class="py-2">{{ $submission->user->name }}</td>
                                    <td>{{ $submission->judul_laporan }}</td>
                                    <td>{{ $submission->status }}</td>
                                    <td>
                                        <a href="{{ route('submissions.show', $submission) }}" class="text-teal-600 dark:text-teal-400 hover:underline">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="mt-2 text-sm text-neutral-400">Tidak ada mahasiswa di sesi ini.</p>
                @endif
            </div>
        @empty
            <p class="text-neutral-500">Tidak ada jadwal sidang hari ini.</p>
        @endforelse
    </div>
</x-layouts::app>