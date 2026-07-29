<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (Gate::allows('dosen')) {
            return $this->dosenDashboard();
        }

        if (Gate::allows('mahasiswa')) {
            return $this->mahasiswaDashboard();
        }

        return $this->adminDashboard();
    }

    protected function dosenDashboard(): View
    {
        $schedules = Schedule::where('tanggal_sidang', today())
            ->whereHas('dosens', fn ($query) => $query->where('users.id', auth()->id()))
            ->with(['submissions.user', 'dosens'])
            ->get();

        return view('dashboard-dosen', compact('schedules'));
    }

    protected function mahasiswaDashboard(): View
    {
        $submission = auth()->user()->submissions()
            ->with('schedule', 'revisionNotes.attachments')
            ->latest()
            ->first();

        return view('dashboard-mahasiswa', compact('submission'));
    }

    protected function adminDashboard(): View
    {
        $stats = [
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'dosen' => User::where('role', 'dosen')->count(),
            'sidang_hari_ini' => Schedule::where('tanggal_sidang', today())->count(),
        ];

        return view('dashboard-admin', compact('stats'));
    }
}
