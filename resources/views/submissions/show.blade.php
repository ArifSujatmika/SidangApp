<x-layouts::app :title="__('Detail Laporan')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">{{ $submission->judul_laporan }}</h1>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 space-y-2">
            <p class="text-sm">Status: <span class="font-medium">{{ $submission->status }}</span></p>
            <p class="text-sm">Mahasiswa: {{ $submission->user->name }}</p>
            <p class="text-sm">NIM: {{ $submission->user->username }}</p>
            @if ($submission->schedule)
                <p class="text-sm">Ruangan: {{ $submission->schedule->ruangan }}</p>
                <p class="text-sm">Tanggal: {{ $submission->schedule->tanggal_sidang->format('d M Y') }}</p>
                <p class="text-sm">Jam: {{ $submission->schedule->jam_mulai }} - {{ $submission->schedule->jam_selesai }}</p>
            @endif

            <div class="pt-2">
                <a href="{{ route('submissions.download', $submission) }}"
                   class="inline-block rounded-lg bg-yellow-500 px-4 py-2 text-sm text-white hover:bg-yellow-600">
                    Download Laporan
                </a>
            </div>
        </div>

        @auth
            @if (auth()->user()->role === 'admin')
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                    <h3 class="text-sm font-medium mb-2">Ubah Status Submission</h3>
                    <form method="POST" action="{{ route('admin.submissions.update-status', $submission) }}" class="flex items-center gap-3">
                        @csrf
                        <select name="status" class="rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
                            <option value="pending" @selected($submission->status === 'pending')>Pending</option>
                            <option value="sidang_berjalan" @selected($submission->status === 'sidang_berjalan')>Sidang Berjalan</option>
                            <option value="revisi" @selected($submission->status === 'revisi')>Revisi</option>
                            <option value="selesai" @selected($submission->status === 'selesai')>Selesai</option>
                        </select>
                        <flux:button type="submit" variant="primary" size="xs">Simpan</flux:button>
                    </form>
                </div>
            @endif
            @if (auth()->user()->role === 'dosen')
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                    <div class="flex items-center gap-3">
                        <flux:button variant="primary" :href="route('revisions.create', $submission)" size="xs">Tambah Catatan Revisi</flux:button>
                    </div>
                </div>
            @endif
            @can('analyze-submission', $submission)
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                    <flux:button variant="primary" :href="route('submissions.analysis', $submission)" size="xs">
                        Analisa AI
                    </flux:button>
                </div>
            @endcan
        @endauth

        @if ($submission->revisionNotes->count())
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                <h2 class="text-lg font-semibold mb-3">Catatan Revisi</h2>
                @foreach ($submission->revisionNotes as $note)
                    <div class="mb-4 border-b pb-3 last:border-0">
                        <p class="text-sm font-medium">{{ $note->dosen->name ?? 'Dosen' }}</p>
                        <p class="text-sm mt-1">{{ $note->catatan_revisi }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs {{ $note->status_poin === 'open' ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $note->status_poin === 'open' ? 'Belum disetujui' : 'Disetujui' }}
                            </span>

                            @if ($note->attachments->count())
                                <span class="text-xs text-neutral-400">| {{ $note->attachments->count() }} tanggapan</span>
                            @endif
                        </div>

                        @if ($note->attachments->count())
                            <div class="mt-2 space-y-1">
                                @foreach ($note->attachments as $attachment)
                                    <div class="rounded bg-neutral-50 dark:bg-neutral-800 p-2 text-sm">
                                        <p>{{ $attachment->keterangan_mahasiswa }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @auth
                            @if (auth()->user()->id === $submission->user_id)
                                <a href="{{ route('revisions.reply', $note) }}" class="mt-2 inline-block text-sm text-yellow-600 dark:text-yellow-400 hover:underline">Balas Revisi</a>
                            @endif
                        @endauth

                        @auth
                            @if (auth()->user()->role === 'dosen' && $note->status_poin === 'open')
                                <form method="POST" action="{{ route('revisions.resolve', $note) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="text-sm text-green-600 hover:underline">
                                        Setujui Revisi
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::app>