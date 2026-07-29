<x-layouts::app :title="__('Dashboard Admin')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Dashboard Admin</h1>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <flux:card class="rounded-xl p-4">
                <p class="text-sm text-neutral-500">Mahasiswa</p>
                <p class="text-3xl font-bold">{{ $stats['mahasiswa'] }}</p>
            </flux:card>
            <flux:card class="rounded-xl p-4">
                <p class="text-sm text-neutral-500">Dosen</p>
                <p class="text-3xl font-bold">{{ $stats['dosen'] }}</p>
            </flux:card>
            <flux:card class="rounded-xl p-4">
                <p class="text-sm text-neutral-500">Sidang Hari Ini</p>
                <p class="text-3xl font-bold">{{ $stats['sidang_hari_ini'] }}</p>
            </flux:card>
        </div>
    </div>
</x-layouts::app>