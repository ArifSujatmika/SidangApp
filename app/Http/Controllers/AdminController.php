<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin');
    }

    public function users(): View
    {
        $users = User::orderBy('role')->orderBy('name')->get();

        return view('admin.users', compact('users'));
    }

    public function createUser(): View
    {
        return view('admin.users-create');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,dosen,mahasiswa',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function editUser(User $user): View
    {
        return view('admin.users-edit', compact('user'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,dosen,mahasiswa',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function schedules(): View
    {
        $schedules = Schedule::withCount('submissions')->with('dosens')->orderBy('tanggal_sidang')->get();

        return view('admin.schedules', compact('schedules'));
    }

    public function createSchedule(): View
    {
        $dosens = User::where('role', 'dosen')->orderBy('name')->get();
        $mahasiswas = User::where('role', 'mahasiswa')->whereDoesntHave('submissions')->orderBy('name')->get();

        return view('admin.schedules-create', compact('dosens', 'mahasiswas'));
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $validated = $this->validateSchedule($request);

        DB::transaction(function () use ($validated): void {
            $schedule = Schedule::create($validated);
            $schedule->dosens()->sync($validated['dosen_ids'] ?? []);
            $this->syncScheduleParticipants($schedule, $validated['peserta_ids'] ?? []);
        });

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function editSchedule(Schedule $schedule): View
    {
        $dosens = User::where('role', 'dosen')->orderBy('name')->get();
        $mahasiswas = User::where('role', 'mahasiswa')->orderBy('name')->get();
        $schedule->load('dosens', 'submissions');

        return view('admin.schedules-edit', compact('schedule', 'dosens', 'mahasiswas'));
    }

    public function updateSchedule(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $this->validateSchedule($request);

        $retainedParticipants = DB::transaction(function () use ($schedule, $validated): array {
            $schedule->update($validated);
            $schedule->dosens()->sync($validated['dosen_ids'] ?? []);

            return $this->syncScheduleParticipants($schedule, $validated['peserta_ids'] ?? []);
        });

        if ($retainedParticipants !== []) {
            return redirect()->route('admin.schedules.index')
                ->with('success', 'Jadwal berhasil diperbarui.')
                ->with('warning', 'Peserta berikut tetap dipertahankan karena sudah mengunggah laporan: '.implode(', ', $retainedParticipants).'.');
        }

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSchedule(Request $request): array
    {
        return $request->validate([
            'nama_grup_sidang' => 'required|string|max:255',
            'ruangan' => 'required|string|max:255',
            'tanggal_sidang' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'dosen_ids' => 'nullable|array',
            'dosen_ids.*' => Rule::exists('users', 'id')->where('role', 'dosen'),
            'peserta_ids' => 'nullable|array',
            'peserta_ids.*' => Rule::exists('users', 'id')->where('role', 'mahasiswa'),
        ]);
    }

    /**
     * @param  array<int, int|string>  $participantIds
     * @return array<int, string>
     */
    protected function syncScheduleParticipants(Schedule $schedule, array $participantIds): array
    {
        $participantIds = array_map('intval', $participantIds);
        $retainedParticipants = [];

        $schedule->submissions()
            ->whereNotIn('user_id', $participantIds ?: [0])
            ->with('user')
            ->get()
            ->each(function (Submission $submission) use (&$retainedParticipants): void {
                if ($submission->file_path !== null) {
                    $retainedParticipants[] = $submission->user->name;

                    return;
                }

                $submission->delete();
            });

        foreach ($participantIds as $participantId) {
            $submission = Submission::where('user_id', $participantId)->latest()->first();

            if ($submission) {
                $submission->update(['schedule_id' => $schedule->id]);

                continue;
            }

            Submission::create([
                'user_id' => $participantId,
                'schedule_id' => $schedule->id,
                'status' => 'pending',
            ]);
        }

        return $retainedParticipants;
    }

    public function destroySchedule(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    public function updateSubmissionStatus(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,sidang_berjalan,revisi,selesai',
        ]);

        $submission->update(['status' => $validated['status']]);

        return back()->with('success', 'Status submit berhasil diperbarui.');
    }
}
