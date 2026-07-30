<?php

return [
    'base_url' => env('AI_API_BASE_URL', 'https://api.openai.com/v1'),
    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL', 'gpt-4o'),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 4096),
    'timeout' => (int) env('AI_TIMEOUT', 120),
    'max_text_length' => (int) env('AI_MAX_TEXT_LENGTH', 50000),
    'retry_attempts' => (int) env('AI_RETRY_ATTEMPTS', 1),
    'assistant_system_prompt' => env('AI_ASSISTANT_SYSTEM_PROMPT'),
];
