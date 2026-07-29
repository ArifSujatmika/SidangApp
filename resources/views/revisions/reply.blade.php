<x-layouts::app :title="__('Tanggapi Revisi')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Tanggapi Revisi</h1>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <p class="text-sm font-medium">Catatan Revisi:</p>
            <p class="text-sm mt-1">{{ $revisionNote->catatan_revisi }}</p>
        </div>

        <form method="POST" action="{{ route('revisions.storeReply', $revisionNote) }}" enctype="multipart/form-data" class="max-w-lg space-y-4">
            @csrf

            <div>
                <label for="keterangan_mahasiswa" class="block text-sm font-medium">Penjelasan</label>
                <textarea name="keterangan_mahasiswa" id="keterangan_mahasiswa" rows="4" required
                    class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800"></textarea>
                @error('keterangan_mahasiswa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="file" class="block text-sm font-medium">File Pendukung (opsional, maks 5MB)</label>
                <input type="file" name="file" id="file" accept=".pdf,.docx,.jpeg,.png"
                    class="mt-1 block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-yellow-500 file:px-4 file:py-2 file:text-white">
                <p class="mt-1 text-xs text-neutral-400">Format: PDF, DOCX, JPEG, PNG. Maks 5MB.</p>
                @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-yellow-500 px-6 py-2 text-white text-sm font-medium hover:bg-yellow-600">
                Kirim Tanggapan
            </button>
        </form>
    </div>
</x-layouts::app>