<x-layouts::app :title="__('Edit Pengguna')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Edit Pengguna</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>Nama</flux:label>
                <input name="name" value="{{ old('name', $user->name) }}" class="w-full rounded border px-3 py-2" required />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Username</flux:label>
                <input name="username" value="{{ old('username', $user->username) }}" class="w-full rounded border px-3 py-2" required />
                <flux:error name="username" />
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full rounded border px-3 py-2" required />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Role</flux:label>
                <select name="role" class="w-full rounded border px-3 py-2">
                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                    <option value="dosen" @selected($user->role === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected($user->role === 'mahasiswa')>Mahasiswa</option>
                </select>
                <flux:error name="role" />
            </flux:field>

            <flux:field>
                <flux:label>Password Baru</flux:label>
                <input name="password" type="password" class="w-full rounded border px-3 py-2" />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>Konfirmasi Password</flux:label>
                <input name="password_confirmation" type="password" class="w-full rounded border px-3 py-2" />
                <flux:error name="password_confirmation" />
            </flux:field>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Simpan</flux:button>
                <a href="{{ route('admin.users.index') }}" class="rounded-lg border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::app>