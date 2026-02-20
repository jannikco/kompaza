<?php

namespace App\Services;

class OpenAIService {
    private string $apiKey;
    private string $apiBase = 'https://api.openai.com/v1';

    public function __construct(?string $apiKey = null) {
        $this->apiKey = $apiKey ?? (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
    }

    public static function isConfigured(): bool {
        return !empty(defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
    }

    /**
     * Send a chat completion request to OpenAI.
     *
     * @param string $systemPrompt System message
     * @param string $userMessage  User message
     * @param string $model        Model to use
     * @return array|null Parsed response content, or null on failure
     */
    public function chatCompletion(string $systemPrompt, string $userMessage, string $model = 'gpt-4o'): ?array {
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
        ];

        try {
            $response = $this->makeRequest('POST', '/chat/completions', $payload);
            $content = $response['choices'][0]['message']['content'] ?? null;
            if (!$content) return null;
            return json_decode($content, true);
        } catch (\Exception $e) {
            error_log('OpenAIService error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate an image using DALL-E.
     *
     * @param string $prompt Image description
     * @param string $size   Image size
     * @return string|null Temporary URL of the generated image (valid ~1 hour)
     */
    public function generateImage(string $prompt, string $size = '1024x1792'): ?string {
        $payload = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'quality' => 'standard',
        ];

        try {
            $response = $this->makeRequest('POST', '/images/generations', $payload);
            return $response['data'][0]['url'] ?? null;
        } catch (\Exception $e) {
            error_log('OpenAI DALL-E error: ' . $e->getMessage());
            return null;
        }
    }

    private function makeRequest(string $method, string $endpoint, ?array $data = null): array {
        $url = $this->apiBase . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('OpenAI cURL error: ' . $curlError);
        }

        $responseData = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
            throw new \Exception("OpenAI API error (HTTP {$httpCode}): {$errorMessage}");
        }

        return $responseData;
    }
}
