<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="mb-4 flex items-center justify-end gap-3">
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-teal-600 dark:text-teal-400 hover:underline">Kelola Pengguna</a>
                    <a href="{{ route('admin.schedules.index') }}" class="text-sm text-teal-600 dark:text-teal-400 hover:underline">Kelola Jadwal</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
                </form>
            @endauth
        </div>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
