<x-layouts::app :title="__('Upload Laporan')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Upload Laporan</h1>

        <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data" class="max-w-lg space-y-4">
            @csrf

            <div>
                <label for="judul_laporan" class="block text-sm font-medium">Judul Laporan</label>
                <input type="text" name="judul_laporan" id="judul_laporan" required
                    class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
                @error('judul_laporan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="schedule_id" class="block text-sm font-medium">Jadwal Sidang</label>
                @if ($schedules->isEmpty())
                    <p class="mt-1 text-sm text-neutral-500">Belum ada jadwal sidang yang ditugaskan kepada Anda.</p>
                @else
                    <select name="schedule_id" id="schedule_id" required
                        class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
                        <option value="">Pilih jadwal</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}">
                                {{ $schedule->nama_grup_sidang }} - {{ $schedule->ruangan }} ({{ $schedule->tanggal_sidang->format('d M Y') }})
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('schedule_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($schedules->isNotEmpty())

            <div>
                <label for="file" class="block text-sm font-medium">File Laporan (PDF, maks 10MB)</label>
                <input type="file" name="file" id="file" accept=".pdf" required
                    class="mt-1 block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-yellow-500 file:px-4 file:py-2 file:text-white">
                @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-yellow-500 px-6 py-2 text-white text-sm font-medium hover:bg-yellow-600">
                Upload
            </button>
            @endif
        </form>
    </div>
</x-layouts::app>