<?php

namespace App\Services\Outreach;

/**
 * Tiny chat-completions client. Prefers SpaceXAI (xAI) when XAI_API_KEY is set,
 * otherwise falls back to the existing OpenAI key so tenants that already
 * generate lead magnets keep working.
 */
final class LlmClient
{
    public static function isConfigured(): bool
    {
        return self::apiKey() !== '';
    }

    public static function provider(): string
    {
        if (defined('XAI_API_KEY') && XAI_API_KEY !== '') {
            return 'xai';
        }
        return 'openai';
    }

    /**
     * @return array<string,mixed>|null decoded JSON object from the model
     */
    public static function chatJson(string $system, string $user, float $temperature = 0.4): ?array
    {
        $key = self::apiKey();
        if ($key === '') {
            return null;
        }

        $xai = self::provider() === 'xai';
        $payload = json_encode([
            'model' => $xai ? 'grok-4.5' : 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'temperature' => $temperature,
            'response_format' => ['type' => 'json_object'],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($xai ? 'https://api.x.ai/v1/chat/completions' : 'https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $key,
                'Content-Type: application/json',
            ],
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $code >= 400) {
            error_log('LlmClient: HTTP ' . $code . ' ' . substr((string) $raw, 0, 300));
            return null;
        }
        $decoded = json_decode((string) $raw, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($content) || $content === '') {
            return null;
        }
        $json = json_decode($content, true);
        return is_array($json) ? $json : null;
    }

    private static function apiKey(): string
    {
        if (defined('XAI_API_KEY') && XAI_API_KEY !== '') {
            return XAI_API_KEY;
        }
        if (defined('OPENAI_API_KEY') && OPENAI_API_KEY !== '') {
            return OPENAI_API_KEY;
        }
        return '';
    }
}
