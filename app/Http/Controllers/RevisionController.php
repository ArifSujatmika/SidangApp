<?php

namespace App\Http\Controllers;

use App\Models\RevisionAttachment;
use App\Models\RevisionNote;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RevisionController extends Controller
{
    public function create(Submission $submission): View
    {
        return view('revisions.create', compact('submission'));
    }

    public function store(Request $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_revisi' => 'required|string|max:2000',
        ]);

        RevisionNote::create([
            'submission_id' => $submission->id,
            'dosen_id' => auth()->id(),
            'catatan_revisi' => $validated['catatan_revisi'],
            'status_poin' => 'open',
        ]);

        return redirect()->route('submissions.show', $submission)->with('success', 'Catatan revisi ditambahkan.');
    }

    public function reply(RevisionNote $revisionNote): View
    {
        $revisionNote->load('submission');

        return view('revisions.reply', compact('revisionNote'));
    }

    public function storeReply(Request $request, RevisionNote $revisionNote): RedirectResponse
    {
        $validated = $request->validate([
            'keterangan_mahasiswa' => 'required|string|max:2000',
            'file' => 'nullable|file|mimes:pdf,docx,jpeg,png|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('revision-attachments', 'local');
        }

        RevisionAttachment::create([
            'revision_note_id' => $revisionNote->id,
            'keterangan_mahasiswa' => $validated['keterangan_mahasiswa'],
            'file_path' => $path ?? '',
        ]);

        return redirect()->route('dashboard')->with('success', 'Tanggapan revisi berhasil dikirim.');
    }

    public function resolve(RevisionNote $revisionNote): RedirectResponse
    {
        $revisionNote->update(['status_poin' => 'resolved']);

        return back()->with('success', 'Poin revisi ditandai selesai.');
    }
}
