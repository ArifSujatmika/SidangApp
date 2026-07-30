<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use App\Services\AiAssistantService;
use Livewire\Component;

class ChatAssistant extends Component
{
    public string $newMessage = '';

    public array $messages = [];

    public bool $loading = false;

    public function mount(): void
    {
        $this->loadMessages();
    }

    public function sendMessage(AiAssistantService $service): void
    {
        $this->validate(['newMessage' => 'required|string|max:2000']);

        $this->loading = true;

        $message = trim($this->newMessage);
        $this->newMessage = '';

        $this->messages[] = ['role' => 'user', 'message' => $message];

        try {
            $reply = $service->chat($message, auth()->id());

            $this->messages[] = ['role' => 'assistant', 'message' => $reply];
        } catch (\Exception $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'message' => 'Maaf, terjadi kesalahan saat memproses pesan Anda. Silakan coba lagi.',
            ];
        }

        $this->loading = false;
    }

    public function clearHistory(AiAssistantService $service): void
    {
        $service->clearHistory(auth()->id());
        $this->loadMessages();
    }

    private function loadMessages(): void
    {
        $this->messages = ChatMessage::forUser(auth()->id() ?? 0)
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.chat-assistant');
    }
}
