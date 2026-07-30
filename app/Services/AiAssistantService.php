<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\DocumentAnalysis;
use App\Models\RevisionNote;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Utils;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use Psr\Http\Message\ResponseInterface;

class AiAssistantService
{
    private const MAX_HISTORY = 20;

    private const MAX_HISTORY_TOKENS = 4000;

    public function chat(string $message, int $userId): string
    {
        $this->saveMessage($userId, 'user', $message);

        $client = $this->createClient();
        $messages = $this->buildMessages($userId, $message);

        $response = $client->chat()->create([
            'model' => config('ai-analysis.model'),
            'max_tokens' => config('ai-analysis.max_tokens'),
            'messages' => $messages,
        ]);

        $reply = $response->choices[0]->message->content ?? 'Maaf, saya tidak dapat memproses permintaan Anda.';

        $this->saveMessage($userId, 'assistant', $reply);

        return $reply;
    }

    public function getConversationHistory(int $userId): array
    {
        return ChatMessage::forUser($userId)
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function clearHistory(int $userId): void
    {
        ChatMessage::forUser($userId)->delete();
    }

    private function saveMessage(int $userId, string $role, string $message): ChatMessage
    {
        return ChatMessage::create([
            'user_id' => $userId,
            'role' => $role,
            'message' => $message,
        ]);
    }

    private function buildMessages(int $userId, string $userMessage): array
    {
        $history = ChatMessage::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->take(self::MAX_HISTORY)
            ->get()
            ->reverse()
            ->values();

        $context = $this->getSystemContext();
        $systemPrompt = $this->buildSystemPrompt($context);
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        $tokenBudget = self::MAX_HISTORY_TOKENS;
        $recentHistory = [];

        foreach ($history as $msg) {
            $tokenEstimate = mb_strlen($msg->message) / 2;
            if ($tokenBudget - $tokenEstimate < 0) {
                break;
            }
            $tokenBudget -= $tokenEstimate;
            $recentHistory[] = $msg;
        }

        $recentHistory = array_reverse($recentHistory);

        foreach ($recentHistory as $msg) {
            $messages[] = [
                'role' => $msg->role === 'assistant' ? 'assistant' : 'user',
                'content' => $msg->message,
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function buildSystemPrompt(array $context): string
    {
        $defaultPrompt = <<<PROMPT
Anda adalah asisten virtual admin untuk Sistem Manajemen Sidang Akademik (SIMSIDANG).
Anda membantu admin menganalisis data sidang dan memberikan insight.

Gunakan Bahasa Indonesia yang sopan dan profesional.

DATA SISTEM SAAT INI:
- Total Pengguna: {$context['total_users']}
  - Admin: {$context['total_admin']}
  - Dosen: {$context['total_dosen']}
  - Mahasiswa: {$context['total_mahasiswa']}
- Total Jadwal Sidang: {$context['total_schedules']}
  - Hari ini: {$context['schedules_today']}
  - Minggu ini: {$context['schedules_this_week']}
- Total Submission: {$context['total_submissions']}
  - Pending: {$context['submissions_pending']}
  - Sidang Berjalan: {$context['submissions_sidang_berjalan']}
  - Revisi: {$context['submissions_revisi']}
  - Selesai: {$context['submissions_selesai']}
- Total Catatan Revisi: {$context['total_revision_notes']}
  - Terbuka: {$context['revision_notes_open']}
  - Selesai: {$context['revision_notes_resolved']}
- Analisa AI Dokumen: {$context['total_analyses']}
  - Rata-rata skor keseluruhan: {$context['avg_overall_score']}/100

Anda dapat membantu:
1. Menganalisis tren dan pola dari data sidang
2. Memberikan rekomendasi berdasarkan data
3. Menjawab pertanyaan tentang data sistem
4. Memberikan insight untuk pengambilan keputusan
5. Menjelaskan fitur dan fungsi sistem

Jawablah dengan singkat, informatif, dan berdasarkan data yang tersedia.
Jika ditanya di luar konteks data sistem, arahkan kembali ke topik manajemen sidang.
PROMPT;

        $prompt = config('ai-analysis.assistant_system_prompt') ?? $defaultPrompt;

        return str_replace(
            array_map(fn ($k) => "{{$k}}", array_keys($context)),
            array_values($context),
            $prompt,
        );
    }

    private function getSystemContext(): array
    {
        $totalUsers = User::count();

        return [
            'total_users' => $totalUsers,
            'total_admin' => User::where('role', 'admin')->count(),
            'total_dosen' => User::where('role', 'dosen')->count(),
            'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'total_schedules' => Schedule::count(),
            'schedules_today' => Schedule::whereDate('tanggal_sidang', today())->count(),
            'schedules_this_week' => Schedule::whereBetween('tanggal_sidang', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_submissions' => Submission::count(),
            'submissions_pending' => Submission::where('status', 'pending')->count(),
            'submissions_sidang_berjalan' => Submission::where('status', 'sidang_berjalan')->count(),
            'submissions_revisi' => Submission::where('status', 'revisi')->count(),
            'submissions_selesai' => Submission::where('status', 'selesai')->count(),
            'total_revision_notes' => RevisionNote::count(),
            'revision_notes_open' => RevisionNote::where('status_poin', 'open')->count(),
            'revision_notes_resolved' => RevisionNote::where('status_poin', 'resolved')->count(),
            'total_analyses' => DocumentAnalysis::count(),
            'avg_overall_score' => round(DocumentAnalysis::avg('overall_score') ?? 0),
        ];
    }

    private function createClient(): OpenAIClient
    {
        $timeout = config('ai-analysis.timeout', 120);

        $handler = HandlerStack::create();
        $handler->push(Middleware::mapResponse(function (ResponseInterface $response): ResponseInterface {
            $body = $response->getBody()->getContents();
            $body = preg_replace('/\s*data:\s*\[DONE\]\s*$/m', '', $body);

            return $response->withBody(Utils::streamFor($body));
        }));

        return OpenAI::factory()
            ->withBaseUri(config('ai-analysis.base_url'))
            ->withApiKey(config('ai-analysis.api_key'))
            ->withHttpClient(new Client(['handler' => $handler, 'timeout' => $timeout]))
            ->make();
    }
}
