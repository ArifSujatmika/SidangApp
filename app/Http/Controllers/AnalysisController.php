<?php

namespace App\Http\Controllers;

use App\Models\Submission;

class AnalysisController extends Controller
{
    public function show(Submission $submission)
    {
        $analysis = $submission->documentAnalysis;

        return view('analysis.show', [
            'submission' => $submission,
            'analysis' => $analysis,
        ]);
    }
}
