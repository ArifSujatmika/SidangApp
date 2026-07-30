<?php

use App\Services\AiAnalysisService;
use Tests\TestCase;

uses(TestCase::class);

test('parseResponse extracts structured data from valid JSON', function () {
    $service = new AiAnalysisService;

    $json = json_encode([
        'summary' => 'Ringkasan test laporan.',
        'plagiarism' => ['score' => 25, 'detail' => 'Konten orisinal'],
        'structure' => ['score' => 85, 'detail' => 'Bab lengkap'],
        'quality' => ['score' => 70, 'detail' => 'Argumen kuat'],
    ]);

    $result = (new ReflectionMethod($service, 'parseResponse'))->invoke($service, $json);

    expect($result)
        ->summary->toBe('Ringkasan test laporan.')
        ->plagiarism_score->toBe(25)
        ->plagiarism_detail->toBe('Konten orisinal')
        ->structure_score->toBe(85)
        ->structure_detail->toBe('Bab lengkap')
        ->quality_score->toBe(70)
        ->quality_detail->toBe('Argumen kuat')
        ->overall_score->toBe(60);
});

test('parseResponse handles missing fields gracefully', function () {
    $service = new AiAnalysisService;

    $json = json_encode(['summary' => 'Hanya ringkasan.']);

    $result = (new ReflectionMethod($service, 'parseResponse'))->invoke($service, $json);

    expect($result)
        ->summary->toBe('Hanya ringkasan.')
        ->plagiarism_score->toBe(0)
        ->structure_score->toBe(0)
        ->quality_score->toBe(0)
        ->overall_score->toBe(0);
});

test('parseResponse throws on invalid JSON', function () {
    $service = new AiAnalysisService;

    expect(fn () => (new ReflectionMethod($service, 'parseResponse'))->invoke($service, 'not-json'))
        ->toThrow(RuntimeException::class, 'AI response is not valid JSON');
});

test('buildPrompt contains document text and instructions', function () {
    $service = new AiAnalysisService;

    $prompt = (new ReflectionMethod($service, 'buildPrompt'))->invoke($service, 'Ini adalah teks laporan test.');

    expect($prompt)
        ->toContain('Ini adalah teks laporan test.')
        ->toContain('Ringkasan')
        ->toContain('plagiarism')
        ->toContain('structure')
        ->toContain('quality');
});

test('buildPrompt truncates text exceeding max length', function () {
    $service = new AiAnalysisService;

    $longText = str_repeat('a', 60000);

    $prompt = (new ReflectionMethod($service, 'buildPrompt'))->invoke($service, $longText);

    expect(mb_strlen($prompt))->toBeLessThan(65000);
});
