<x-layouts::app :title="__('Manage Schedules')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Kelola Jadwal Sidang</h1>
            <a href="{{ route('admin.schedules.create') }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white hover:bg-teal-700">Tambah Jadwal</a>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-full text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Grup</th>
                        <th class="px-4 py-3 text-left">Ruangan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jam</th>
                        <th class="px-4 py-3 text-left">Dosen Penguji</th>
                        <th class="px-4 py-3 text-left">Submissions</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $schedule->nama_grup_sidang }}</td>
                            <td class="px-4 py-3">{{ $schedule->ruangan }}</td>
                            <td class="px-4 py-3">{{ $schedule->tanggal_sidang->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }}</td>
                            <td class="px-4 py-3">{{ $schedule->dosens->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-3">{{ $schedule->submissions_count }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.schedules.edit', $schedule) }}" class="text-teal-600 dark:text-teal-400 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus jadwal {{ $schedule->nama_grup_sidang }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::app>
