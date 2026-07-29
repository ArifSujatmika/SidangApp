<x-layouts::app :title="__('Manage Users')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Kelola Pengguna</h1>
            <flux:button variant="primary" :href="route('admin.users.create')" wire:navigate>Tambah Pengguna</flux:button>
        </div>

        <flux:table class="min-w-full">
            <flux:table.columns>
                <flux:table.column>Nama</flux:table.column>
                <flux:table.column>Username</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row>
                        <flux:table.cell>{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->username }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>{{ $user->role }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button variant="ghost" size="xs" :href="route('admin.users.edit', $user)" wire:navigate>Edit</flux:button>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button variant="ghost" size="xs" type="submit" class="!text-red-600">Hapus</flux:button>
                                </form>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>