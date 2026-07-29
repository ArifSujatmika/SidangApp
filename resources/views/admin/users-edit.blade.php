<x-layouts::app :title="__('Edit Pengguna')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Edit Pengguna</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm">Nama</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="w-full rounded border px-3 py-2" required>
            </div>
            <div>
                <label class="mb-1 block text-sm">Username</label>
                <input name="username" value="{{ old('username', $user->username) }}" class="w-full rounded border px-3 py-2" required>
            </div>
            <div>
                <label class="mb-1 block text-sm">Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full rounded border px-3 py-2" required>
            </div>
            <div>
                <label class="mb-1 block text-sm">Role</label>
                <select name="role" class="w-full rounded border px-3 py-2">
                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                    <option value="dosen" @selected($user->role === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected($user->role === 'mahasiswa')>Mahasiswa</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm">Password Baru</label>
                <input name="password" type="password" class="w-full rounded border px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm">Konfirmasi Password</label>
                <input name="password_confirmation" type="password" class="w-full rounded border px-3 py-2">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white hover:bg-teal-700">Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-lg border px-4 py-2 text-sm">Batal</a>
            </div>
        </form>
    </div>
</x-layouts::app>
