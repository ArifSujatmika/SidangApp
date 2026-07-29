<x-layouts::app :title="__('Dashboard Mahasiswa')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Dashboard Mahasiswa</h1>

        @if ($submission)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                <h2 class="text-lg font-semibold">{{ $submission->judul_laporan ?? 'Laporan belum dilengkapi' }}</h2>
                <p class="text-sm text-neutral-500">
                    Status: <span class="font-medium">{{ $submission->status }}</span>
                    @if ($submission->schedule)
                        | Tanggal Sidang: {{ $submission->schedule->tanggal_sidang->format('d M Y') }} | {{ $submission->schedule->ruangan }} | {{ $submission->schedule->jam_mulai }} - {{ $submission->schedule->jam_selesai }}
                    @endif
                </p>
                @if ($submission->file_path === null)
                    <flux:button variant="primary" size="xs" :href="route('submissions.edit', $submission)">Lengkapi Laporan</flux:button>
                @else
                    <flux:button variant="ghost" size="xs" :href="route('submissions.show', $submission)">Lihat Detail</flux:button>
                @endif
            </div>

            @if ($submission->revisionNotes->count())
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                    <h3 class="text-lg font-semibold mb-3">Catatan Revisi</h3>
                    @foreach ($submission->revisionNotes as $note)
                        <div class="mb-4 border-b pb-3 last:border-0">
                            <p class="text-sm">{{ $note->catatan_revisi }}</p>
                            <p class="text-xs text-neutral-400 mt-1">
                                Status: {{ $note->status_poin }}
                            </p>

                            @if ($note->attachments->count())
                                <div class="mt-2 space-y-1">
                                    @foreach ($note->attachments as $attachment)
                                        <div class="rounded bg-neutral-50 dark:bg-neutral-800 p-2 text-sm">
                                            <p>{{ $attachment->keterangan_mahasiswa ?? 'Tidak ada keterangan' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($note->status_poin === 'open')
                                <a href="{{ route('revisions.reply', $note) }}" class="text-yellow-600 dark:text-yellow-400 hover:underline text-sm mt-2 inline-block">
                                    + Tanggapi Revisi
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                <p class="text-neutral-500">Belum ada submission.</p>
                <flux:button variant="ghost" :href="route('submissions.create')" size="xs">+ Upload Laporan</flux:button>
            </div>
        @endif
    </div>
</x-layouts::app>
