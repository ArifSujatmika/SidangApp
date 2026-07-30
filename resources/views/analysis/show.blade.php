<x-layouts::app :title="__('Analisa AI')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Analisa AI — {{ $submission->judul_laporan }}</h1>
            <a href="{{ route('submissions.show', $submission) }}"
               class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400">
                &larr; Kembali
            </a>
        </div>

        <livewire:document-analyzer :submission="$submission" :key="'analyzer-'.$submission->id" />
    </div>
</x-layouts::app>