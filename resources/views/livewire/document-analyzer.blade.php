@php
    $status = $analysis?->status;
    $hasFile = !empty($submission->file_path);
@endphp

<div x-data="{ open: null }" wire:poll.2s="refreshStatus">
    @if (!$hasFile)
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-8 text-center">
            <p class="text-neutral-500 dark:text-neutral-400">Submission belum memiliki file. Upload laporan terlebih dahulu.</p>
        </div>
    @elseif ($status === 'processing')
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-8 text-center">
            <flux:icon.loader-circle class="mx-auto mb-4 size-8 animate-spin text-yellow-500" />
            <p class="text-neutral-600 dark:text-neutral-300">Sedang menganalisa dokumen...</p>
            <p class="text-xs text-neutral-400 mt-2">Proses ini membutuhkan 1-2 menit</p>
        </div>
    @elseif ($status === 'failed')
        <div class="rounded-xl border border-red-200 dark:border-red-800 p-8 text-center">
            <flux:icon.triangle-alert class="mx-auto mb-4 size-8 text-red-500" />
            <p class="font-medium text-red-700 dark:text-red-400 mb-2">Analisa Gagal</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">{{ $analysis->error_message }}</p>
            <flux:button variant="primary" wire:click="retry" size="base">
                Coba Lagi
            </flux:button>
        </div>
    @elseif ($status === 'completed')
        @php
            $scores = [
                'plagiarism' => ['label' => 'Plagiarisme', 'score' => $analysis->plagiarism_score, 'detail' => $analysis->plagiarism_detail, 'color' => $analysis->plagiarism_score > 70 ? 'red' : ($analysis->plagiarism_score > 40 ? 'yellow' : 'green')],
                'structure' => ['label' => 'Struktur', 'score' => $analysis->structure_score, 'detail' => $analysis->structure_detail, 'color' => $analysis->structure_score > 70 ? 'red' : ($analysis->structure_score > 40 ? 'yellow' : 'green')],
                'quality' => ['label' => 'Kualitas', 'score' => $analysis->quality_score, 'detail' => $analysis->quality_detail, 'color' => $analysis->quality_score > 70 ? 'red' : ($analysis->quality_score > 40 ? 'yellow' : 'green')],
            ];
        @endphp

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 text-center">
            <p class="text-sm text-neutral-500 mb-2">Skor Keseluruhan</p>
            <span class="text-5xl font-bold {{ $analysis->overall_score > 70 ? 'text-red-500' : ($analysis->overall_score > 40 ? 'text-yellow-500' : 'text-green-500') }}">
                {{ $analysis->overall_score }}
            </span>
            <span class="text-2xl text-neutral-400">/100</span>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <h3 class="text-sm font-medium mb-3 text-neutral-500 uppercase tracking-wide">Ringkasan</h3>
            <p class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">{{ $analysis->summary }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($scores as $key => $score)
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium">{{ $score['label'] }}</span>
                        <span class="text-2xl font-bold
                            {{ $score['color'] === 'red' ? 'text-red-500' : ($score['color'] === 'yellow' ? 'text-yellow-500' : 'text-green-500') }}">
                            {{ $score['score'] }}
                        </span>
                    </div>
                    <button @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                            class="text-xs text-yellow-600 dark:text-yellow-400 hover:underline"
                            x-text="open === '{{ $key }}' ? 'Sembunyikan' : 'Lihat detail'">
                    </button>
                    <div x-show="open === '{{ $key }}'" x-cloak class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
                        {{ $score['detail'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center pt-2">
            <flux:button variant="subtle" wire:click="retry" size="sm">
                Analisa Ulang
            </flux:button>
        </div>
    @else
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-8 text-center">
            @if ($status === 'pending')
                <flux:icon.loader-circle class="mx-auto mb-4 size-8 animate-spin text-yellow-500" />
                <p class="text-neutral-600 dark:text-neutral-300">Menunggu analisa...</p>
            @else
                <p class="text-neutral-600 dark:text-neutral-300 mb-4">Belum ada analisa untuk laporan ini.</p>
                <flux:button variant="primary" wire:click="triggerAnalysis" size="base">
                    Mulai Analisa AI
                </flux:button>
            @endif
        </div>
    @endif
</div>