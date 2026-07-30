<div class="flex h-[calc(100vh-12rem)] flex-col rounded-xl border border-neutral-200 dark:border-neutral-700">
    <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3 dark:border-neutral-700">
        <div class="flex items-center gap-2">
            <flux:icon.sparkles class="size-5 text-yellow-500" />
            <h2 class="font-semibold">Asisten AI</h2>
        </div>
        @if (count($messages) > 0)
            <flux:button variant="ghost" size="sm" wire:click="clearHistory" wire:confirm="Hapus semua percakapan?">
                Hapus Riwayat
            </flux:button>
        @endif
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4" x-data x-ref="chatbox" x-init="$nextTick(() => $refs.chatbox.scrollTop = $refs.chatbox.scrollHeight)">
        @forelse ($messages as $msg)
            @if ($msg['role'] === 'assistant')
                <div class="flex items-start gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                        <flux:icon.sparkles class="size-4 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="rounded-xl bg-neutral-100 px-4 py-2.5 text-sm leading-relaxed text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 text-left whitespace-pre-wrap">
                        {{ $msg['message'] }}
                    </div>
                </div>
            @else
                <div class="flex items-start justify-end gap-3">
                    <div class="rounded-xl bg-yellow-500 px-4 py-2.5 text-sm leading-relaxed text-white text-left whitespace-pre-wrap">
                        {{ $msg['message'] }}
                    </div>
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <flux:icon.user class="size-4 text-neutral-600 dark:text-neutral-300" />
                    </div>
                </div>
            @endif
        @empty
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <flux:icon.sparkles class="mb-3 size-10 text-neutral-300 dark:text-neutral-600" />
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Tanya apapun tentang data sidang</p>
                <p class="mt-1 text-xs text-neutral-400 dark:text-neutral-500">Contoh: "Berapa mahasiswa yang sudah sidang?", "Tampilkan jadwal hari ini"</p>
            </div>
        @endforelse

        @if ($loading)
            <div class="flex items-start gap-3">
                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                    <flux:icon.sparkles class="size-4 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="flex items-center gap-1.5 rounded-xl bg-neutral-100 px-4 py-3 dark:bg-neutral-800">
                    <span class="size-1.5 animate-bounce rounded-full bg-neutral-400" style="animation-delay:0ms"></span>
                    <span class="size-1.5 animate-bounce rounded-full bg-neutral-400" style="animation-delay:150ms"></span>
                    <span class="size-1.5 animate-bounce rounded-full bg-neutral-400" style="animation-delay:300ms"></span>
                </div>
            </div>
        @endif
    </div>

    <div class="border-t border-neutral-200 p-4 dark:border-neutral-700">
        <form wire:submit="sendMessage" class="flex gap-2">
            <flux:input
                wire:model.live="newMessage"
                placeholder="Ketik pesan..."
                class="flex-1"
                :disabled="$loading"
            />
            <flux:button variant="primary" type="submit" :disabled="$loading || trim($newMessage) === ''">
                Kirim
            </flux:button>
        </form>
    </div>
</div>
