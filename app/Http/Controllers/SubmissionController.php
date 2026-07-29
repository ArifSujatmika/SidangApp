<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $submission = auth()->user()->submissions()->latest()->first();

        if ($submission && $submission->file_path === null) {
            return redirect()->route('submissions.edit', $submission);
        }

        $schedules = Schedule::where('tanggal_sidang', '>=', today())
            ->whereHas('submissions', fn ($query) => $query->where('user_id', auth()->id()))
            ->orderBy('tanggal_sidang')
            ->get();

        return view('submissions.create', compact('schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_id' => [
                'required',
                'exists:schedules,id',
                function ($attribute, $value, $fail) {
                    $exists = Submission::where('user_id', auth()->id())
                        ->where('schedule_id', $value)
                        ->exists();

                    if (! $exists) {
                        $fail('Jadwal sidang tidak valid untuk akun Anda.');
                    }
                },
            ],
            'judul_laporan' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $request->file('file')->store('submissions', 'local');

        Submission::create([
            'user_id' => auth()->id(),
            'schedule_id' => $validated['schedule_id'],
            'judul_laporan' => $validated['judul_laporan'],
            'file_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil diupload.');
    }

    public function edit(Submission $submission): View
    {
        abort_unless($submission->user_id === auth()->id() && $submission->file_path === null, 403);
        $submission->load('schedule');

        return view('submissions.edit', compact('submission'));
    }

    public function update(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless($submission->user_id === auth()->id() && $submission->file_path === null, 403);

        $validated = $request->validate([
            'judul_laporan' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $submission->update([
            'judul_laporan' => $validated['judul_laporan'],
            'file_path' => $request->file('file')->store('submissions', 'local'),
        ]);

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil dilengkapi.');
    }

    public function show(Submission $submission): View
    {
        $submission->load('user', 'schedule', 'revisionNotes.attachments');

        return view('submissions.show', compact('submission'));
    }

    public function download(Submission $submission): StreamedResponse
    {
        if (! Gate::allows('download-submission', $submission)) {
            abort(403);
        }

        abort_if($submission->file_path === null, 404);

        return Storage::disk('local')->download($submission->file_path);
    }
}
