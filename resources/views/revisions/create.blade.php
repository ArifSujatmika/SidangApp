<x-layouts::app :title="__('Tambah Catatan Revisi')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Tambah Catatan Revisi</h1>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
            <p class="text-sm font-medium">Laporan: {{ $submission->judul_laporan }}</p>
            <p class="text-sm">Mahasiswa: {{ $submission->user->name }} ({{ $submission->user->username }})</p>
        </div>

        <form method="POST" action="{{ route('revisions.store', $submission) }}" class="max-w-lg space-y-4">
            @csrf

            <div>
                <label for="catatan_revisi" class="block text-sm font-medium">Catatan Revisi</label>
                <textarea name="catatan_revisi" id="catatan_revisi" rows="5" required
                    class="mt-1 block w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800"
                    placeholder="Tuliskan poin revisi..."></textarea>
                @error('catatan_revisi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-yellow-500 px-6 py-2 text-white text-sm font-medium hover:bg-yellow-600">
                Simpan Catatan
            </button>
        </form>
    </div>
</x-layouts::app>