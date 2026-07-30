<?php

namespace App\Services;

use GuzzleHttp\Client;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use Smalot\PdfParser\Parser;

class AiAnalysisService
{
    public function __construct(private readonly ?string $baseUrl = null) {}

    public function extractText(string $pdfPath): string
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);

        return $pdf->getText();
    }

    public function analyze(string $text): array
    {
        $client = $this->createClient();
        $prompt = $this->buildPrompt($text);

        $response = $client->chat()->create([
            'model' => config('ai-analysis.model'),
            'max_tokens' => config('ai-analysis.max_tokens'),
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah asisten analisis dokumen akademik yang memberikan output dalam format JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        $content = $response->choices[0]->message->content;

        return $this->parseResponse($content);
    }

    private function createClient(): OpenAIClient
    {
        $timeout = config('ai-analysis.timeout', 120);

        return OpenAI::factory()
            ->withBaseUri($this->baseUrl ?? config('ai-analysis.base_url'))
            ->withApiKey(config('ai-analysis.api_key'))
            ->withHttpClient(new Client(['timeout' => $timeout]))
            ->make();
    }

    private function buildPrompt(string $text): string
    {
        $maxLength = config('ai-analysis.max_text_length', 50000);
        $truncated = mb_substr($text, 0, $maxLength);

        return <<<PROMPT
Analisis dokumen laporan akademik berikut dalam Bahasa Indonesia. Berikan output dalam format JSON dengan struktur:

{
  "summary": "Ringkasan laporan dalam 200-300 kata, Bahasa Indonesia",
  "plagiarism": {
    "score": integer (0-100, estimasi tingkat orisinalitas, 100 = sangat orisinal),
    "detail": "Penjelasan estimasi plagiarisme"
  },
  "structure": {
    "score": integer (0-100, kelengkapan struktur seperti bab, daftar isi, daftar pustaka, format penulisan),
    "detail": "Evaluasi struktur dan format dokumen"
  },
  "quality": {
    "score": integer (0-100, kualitas argumen, metodologi, kedalaman analisis),
    "detail": "Saran perbaikan spesifik"
  }
}

Dokumen:
{$truncated}
PROMPT;
    }

    private function parseResponse(string $json): array
    {
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new \RuntimeException('AI response is not valid JSON');
        }

        $plagiarism = $data['plagiarism'] ?? [];
        $structure = $data['structure'] ?? [];
        $quality = $data['quality'] ?? [];

        $plagiarismScore = (int) ($plagiarism['score'] ?? 0);
        $structureScore = (int) ($structure['score'] ?? 0);
        $qualityScore = (int) ($quality['score'] ?? 0);
        $overall = (int) round(($plagiarismScore + $structureScore + $qualityScore) / 3);

        return [
            'summary' => $data['summary'] ?? '',
            'plagiarism_score' => $plagiarismScore,
            'plagiarism_detail' => $plagiarism['detail'] ?? '',
            'structure_score' => $structureScore,
            'structure_detail' => $structure['detail'] ?? '',
            'quality_score' => $qualityScore,
            'quality_detail' => $quality['detail'] ?? '',
            'overall_score' => $overall,
            'raw_response' => $data,
        ];
    }
}
