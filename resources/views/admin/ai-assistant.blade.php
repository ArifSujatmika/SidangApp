<x-layouts::app :title="__('Asisten AI')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Asisten AI</h1>
        </div>
        <livewire:chat-assistant />
    </div>
</x-layouts::app>
