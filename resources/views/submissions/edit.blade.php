<x-layouts::app :title="__('Lengkapi Laporan')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Lengkapi Laporan Sidang</h1>

        <div class="max-w-lg rounded-xl border border-teal-100 bg-teal-50/40 p-4 dark:border-neutral-700 dark:bg-neutral-800">
            <p class="font-medium">{{ $submission->schedule->nama_grup_sidang }}</p>
            <p class="text-sm text-neutral-500">{{ $submission->schedule->ruangan }} · {{ $submission->schedule->tanggal_sidang->format('d M Y') }} · {{ $submission->schedule->jam_mulai }}–{{ $submission->schedule->jam_selesai }}</p>
        </div>

        <form method="POST" action="{{ route('submissions.update', $submission) }}" enctype="multipart/form-data" class="max-w-lg space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="judul_laporan" class="block text-sm font-medium">Judul Laporan</label>
                <input type="text" name="judul_laporan" id="judul_laporan" value="{{ old('judul_laporan', $submission->judul_laporan) }}" required
                    class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
                @error('judul_laporan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="file" class="block text-sm font-medium">File Laporan (PDF, maks 10MB)</label>
                <input type="file" name="file" id="file" accept=".pdf" required
                    class="mt-1 block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:text-white">
                @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-teal-600 px-6 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan Laporan</button>
        </form>
    </div>
</x-layouts::app>
