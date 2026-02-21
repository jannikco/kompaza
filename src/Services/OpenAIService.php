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

    /**
     * Run multiple chat completion requests in parallel using curl_multi.
     *
     * @param array $requests Array of [{key, system, user, model?, max_tokens?, temperature?}]
     * @param int $timeout Timeout in seconds
     * @return array Keyed array: ['key' => parsed_json|null, ...]
     */
    public function parallelChatCompletions(array $requests, int $timeout = 60): array {
        $multiHandle = curl_multi_init();
        $handles = [];

        foreach ($requests as $req) {
            $payload = [
                'model' => $req['model'] ?? 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $req['system']],
                    ['role' => 'user', 'content' => $req['user']],
                ],
                'temperature' => $req['temperature'] ?? 0.7,
                'response_format' => ['type' => 'json_object'],
            ];
            if (isset($req['max_tokens'])) {
                $payload['max_tokens'] = $req['max_tokens'];
            }

            $ch = $this->buildCurlHandle('POST', '/chat/completions', $payload, $timeout);
            $handles[$req['key']] = $ch;
            curl_multi_add_handle($multiHandle, $ch);
        }

        // Execute all requests
        $running = null;
        do {
            $status = curl_multi_exec($multiHandle, $running);
            if ($running > 0) {
                curl_multi_select($multiHandle, 1);
            }
        } while ($running > 0 && $status === CURLM_OK);

        // Collect results
        $results = [];
        foreach ($handles as $key => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $parsed = null;

            if ($response && $httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                $content = $responseData['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    $parsed = json_decode($content, true);
                }
            } else {
                error_log("OpenAI parallel call '{$key}' failed: HTTP {$httpCode}");
            }

            $results[$key] = $parsed;
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);
        return $results;
    }

    /**
     * Phase 1: Analyze PDF — runs only the orchestrator call (~5s).
     * Returns language, metadata, cover_prompt, etc.
     */
    public function analyzeLeadMagnet(string $pdfText, string $context = ''): ?array {
        $userMessage = "Here is the content of the PDF lead magnet:\n\n" . $pdfText;
        if (!empty($context)) {
            $userMessage .= "\n\nAdditional context from the author about the target audience and goals:\n" . $context;
        }

        $orchestratorSystem = <<<'PROMPT'
You are a content analysis expert. Analyze the PDF content provided and extract key metadata.

CRITICAL: Detect the language of the PDF content. If the PDF is written in Danish, ALL text fields MUST be in Danish. If the PDF is in English, write in English. Never translate — always match the source language exactly.

Return a JSON object with exactly these fields:
- "language": ISO 639-1 code of the detected language (e.g. "da", "en", "de")
- "content_summary": 2-3 sentence summary of the PDF content (in detected language)
- "key_topics": array of 5-8 key topics/themes from the PDF (in detected language)
- "tone": the writing tone - one of "professional", "casual", "academic", "friendly"
- "title": a clear, descriptive title for the lead magnet (max 60 chars, in detected language)
- "slug": URL-friendly slug derived from the title (lowercase, hyphens, no special chars)
- "subtitle": a supporting subtitle (1 sentence, max 120 chars, in detected language)
- "meta_description": SEO meta description (max 155 chars, in detected language)
- "hero_bg_color": a professional dark hex color for the hero background (e.g. "#1e3a5f")
- "cover_prompt": a DALL-E image generation prompt describing an ideal abstract book cover. Focus on visual elements, colors, mood, and abstract shapes only. Do NOT include any text or typography. This field must always be in English for DALL-E.
PROMPT;

        return $this->chatCompletion($orchestratorSystem, $userMessage);
    }

    /**
     * Phase 2: Generate sections — takes confirmed language, runs 3 parallel calls, merges results.
     * Uses the user-confirmed $language (not the auto-detected one).
     */
    public function generateLeadMagnetSections(string $pdfText, array $orchestratorResult, string $language, string $context = ''): array {
        $tone = $orchestratorResult['tone'] ?? 'professional';
        $summary = $orchestratorResult['content_summary'] ?? '';
        $keyTopics = $orchestratorResult['key_topics'] ?? [];

        $contextBlock = "TARGET LANGUAGE: {$language} (you MUST write ALL output in this language)\n";
        $contextBlock .= "WRITING TONE: {$tone}\n";
        $contextBlock .= "CONTENT SUMMARY: {$summary}\n";
        $contextBlock .= "KEY TOPICS: " . implode(', ', $keyTopics) . "\n\n";
        $contextBlock .= "PDF CONTENT:\n" . $pdfText;
        if (!empty($context)) {
            $contextBlock .= "\n\nADDITIONAL CONTEXT FROM AUTHOR:\n" . $context;
        }

        $parallelRequests = [
            [
                'key' => 'hero_email',
                'system' => <<<PROMPT
You are a marketing copywriter. Generate hero section copy and email delivery content for a lead magnet landing page.

CRITICAL: Write ALL output in the language specified (see TARGET LANGUAGE). Never switch to English unless the target language IS English.

Return a JSON object with exactly these fields:
- "variants": an array of exactly 3 hero variant objects. Each variant has:
  - "hero_headline": an attention-grabbing headline (max 10 words, punchy and benefit-driven)
  - "hero_headline_accent": the 2-3 most impactful/important words from that headline (must be a substring of hero_headline)
  - "hero_subheadline": supporting text below the headline (1-2 sentences, explains the value)
  - "hero_cta_text": call-to-action button text (2-4 words)
  - "hero_badge": a short context/urgency label (max 5 words, e.g. "Free Guide", "Exclusive PDF", "New Ebook")
  Variant 1: Bold & direct style. Variant 2: Curiosity-driven style. Variant 3: Benefit-focused style.
- "email_subject": email subject line for delivering the PDF (friendly, enticing)
- "email_body_html": a short, friendly HTML email body that delivers the download link. Use {{name}} for the recipient's name and {{download_link}} for the PDF download URL. Keep it concise (3-5 short paragraphs). Use simple HTML (p tags, a tag for the link). Make it warm and {$tone}.
PROMPT,
                'user' => $contextBlock,
                'max_tokens' => 3000,
            ],
            [
                'key' => 'content_sections',
                'system' => <<<'PROMPT'
You are a content structure expert. Extract structured content sections from the PDF for a landing page.

CRITICAL: Write ALL output in the language specified (see TARGET LANGUAGE). Never switch to English unless the target language IS English.

Return a JSON object with exactly these fields:
- "features_headline": a headline for the features/benefits section (e.g. "What You'll Learn")
- "features": array of 3-6 objects, each with "title" (short, 3-6 words) and "description" (1 sentence). These highlight key takeaways from the PDF.
- "chapters": array of up to 8 objects, each with "number" (integer), "title" (chapter/section title), and "description" (1 sentence summary). This represents the table of contents or main sections of the PDF. If the PDF doesn't have clear chapters, create logical sections based on the content.
- "key_statistics": array of 3-4 objects, each with "value" (a number, percentage, or short metric), "label" (what the number represents), and "icon" (a single relevant emoji). Extract real numbers/data from the PDF where possible, or create compelling statistics about the content.
PROMPT,
                'user' => $contextBlock,
                'max_tokens' => 3000,
            ],
            [
                'key' => 'trust_sections',
                'system' => <<<'PROMPT'
You are a conversion optimization expert. Generate trust-building and audience-targeting content for a lead magnet landing page.

CRITICAL: Write ALL output in the language specified (see TARGET LANGUAGE). Never switch to English unless the target language IS English.

Return a JSON object with exactly these fields:
- "target_audience": array of exactly 3 objects, each with "icon" (a single relevant emoji), "title" (short persona name, 3-5 words), and "description" (1 sentence explaining why this persona benefits from the PDF)
- "faq": array of 4-5 objects, each with "question" and "answer". Common questions a prospect might have before downloading. Keep answers concise (1-2 sentences).
- "before_after": an object with "before" (array of 3 pain point strings that the reader experiences without this knowledge) and "after" (array of 3 positive outcome strings after reading the PDF). Keep each string to 1 short sentence.
- "author_bio_suggestion": a 2-3 sentence author bio based on the expertise demonstrated in the PDF. Write it in third person.
- "testimonial_templates": array of 2-3 objects, each with "quote" (a realistic testimonial quote), "name" (a realistic first name), and "title" (a job title). These are editable templates for the user to customize with real feedback.
- "social_proof": array of 3 objects, each with "value" (a compelling metric like "50+" or "10,000+"), "label" (what it represents, e.g. "Pages of Insights"), and "icon" (a single emoji). These replace the default metrics bar on the landing page.
- "section_headings": an object with translated section heading strings for the landing page. All values must be in the TARGET LANGUAGE. Keys and their English defaults: "form_title" ("Get Your Free Copy"), "form_subtitle" ("Enter your details below and we'll send it straight to your inbox."), "form_name_label" ("Full Name"), "form_email_label" ("Email Address"), "form_privacy" ("We respect your privacy. Unsubscribe at any time."), "form_sending" ("Sending..."), "toc_title" ("Table of Contents"), "toc_subtitle" ("A preview of what you'll find inside"), "stats_title" ("By the Numbers"), "transformation_title" ("The Transformation"), "before_label" ("Before"), "after_label" ("After"), "audience_title" ("Who Is This For?"), "author_title" ("About the Author"), "testimonials_title" ("What Readers Say"), "faq_title" ("Frequently Asked Questions"), "cta_title" ("Ready to Get Started?"), "cta_subtitle" ("Download your free copy now and start implementing today."), "default_proof_1" ("PDF Guide"), "default_proof_2" ("100% Free"), "default_proof_3" ("Instant Access")
PROMPT,
                'user' => $contextBlock,
                'max_tokens' => 4000,
            ],
        ];

        $parallelResults = $this->parallelChatCompletions($parallelRequests, 90);

        // Merge results
        $merged = [
            'language' => $language,
            'title' => $orchestratorResult['title'] ?? '',
            'slug' => $orchestratorResult['slug'] ?? '',
            'subtitle' => $orchestratorResult['subtitle'] ?? '',
            'meta_description' => $orchestratorResult['meta_description'] ?? '',
            'hero_bg_color' => $orchestratorResult['hero_bg_color'] ?? '#1e3a5f',
            'cover_prompt' => $orchestratorResult['cover_prompt'] ?? '',
        ];

        $failedKeys = [];

        if ($parallelResults['hero_email']) {
            $he = $parallelResults['hero_email'];
            // Store full variants array for the wizard's "Pick Style" step
            $merged['hero_variants'] = $he['variants'] ?? [];
            // Default to first variant for backward-compat flat fields
            $firstVariant = $merged['hero_variants'][0] ?? [];
            $merged['hero_headline'] = $firstVariant['hero_headline'] ?? '';
            $merged['hero_subheadline'] = $firstVariant['hero_subheadline'] ?? '';
            $merged['hero_cta_text'] = $firstVariant['hero_cta_text'] ?? '';
            $merged['hero_badge'] = $firstVariant['hero_badge'] ?? '';
            $merged['hero_headline_accent'] = $firstVariant['hero_headline_accent'] ?? '';
            $merged['email_subject'] = $he['email_subject'] ?? '';
            $merged['email_body_html'] = $he['email_body_html'] ?? '';
        } else {
            $failedKeys[] = 'hero_email';
        }

        if ($parallelResults['content_sections']) {
            $cs = $parallelResults['content_sections'];
            $merged['features_headline'] = $cs['features_headline'] ?? '';
            $merged['features'] = $cs['features'] ?? [];
            $merged['chapters'] = $cs['chapters'] ?? [];
            $merged['key_statistics'] = $cs['key_statistics'] ?? [];
        } else {
            $failedKeys[] = 'content_sections';
        }

        if ($parallelResults['trust_sections']) {
            $ts = $parallelResults['trust_sections'];
            $merged['target_audience'] = $ts['target_audience'] ?? [];
            $merged['faq'] = $ts['faq'] ?? [];
            $merged['before_after'] = $ts['before_after'] ?? ['before' => [], 'after' => []];
            $merged['author_bio'] = $ts['author_bio_suggestion'] ?? '';
            $merged['testimonial_templates'] = $ts['testimonial_templates'] ?? [];
            $merged['social_proof'] = $ts['social_proof'] ?? [];
            $merged['section_headings'] = $ts['section_headings'] ?? [];
        } else {
            $failedKeys[] = 'trust_sections';
        }

        return [
            'success' => true,
            'data' => $merged,
            'errors' => $failedKeys,
            'partial' => !empty($failedKeys),
        ];
    }

    /**
     * Orchestrated multi-step AI generation for lead magnet content.
     *
     * Step 1: Orchestrator analyzes PDF (sequential)
     * Step 2: 3 specialized calls run in parallel
     * Step 3: Merge results
     *
     * @param string $pdfText Extracted PDF text
     * @param string $context Optional additional context from user
     * @return array ['success' => bool, 'data' => array, 'errors' => array, 'partial' => bool]
     */
    public function generateLeadMagnetContent(string $pdfText, string $context = ''): array {
        $userMessage = "Here is the content of the PDF lead magnet:\n\n" . $pdfText;
        if (!empty($context)) {
            $userMessage .= "\n\nAdditional context from the author about the target audience and goals:\n" . $context;
        }

        // Step 1: Orchestrator — analyze PDF, detect language, extract metadata
        $orchestratorSystem = <<<'PROMPT'
You are a content analysis expert. Analyze the PDF content provided and extract key metadata.

CRITICAL: Detect the language of the PDF content. If the PDF is written in Danish, ALL text fields MUST be in Danish. If the PDF is in English, write in English. Never translate — always match the source language exactly.

Return a JSON object with exactly these fields:
- "language": ISO 639-1 code of the detected language (e.g. "da", "en", "de")
- "content_summary": 2-3 sentence summary of the PDF content (in detected language)
- "key_topics": array of 5-8 key topics/themes from the PDF (in detected language)
- "tone": the writing tone - one of "professional", "casual", "academic", "friendly"
- "title": a clear, descriptive title for the lead magnet (max 60 chars, in detected language)
- "slug": URL-friendly slug derived from the title (lowercase, hyphens, no special chars)
- "subtitle": a supporting subtitle (1 sentence, max 120 chars, in detected language)
- "meta_description": SEO meta description (max 155 chars, in detected language)
- "hero_bg_color": a professional dark hex color for the hero background (e.g. "#1e3a5f")
- "cover_prompt": a DALL-E image generation prompt describing an ideal abstract book cover. Focus on visual elements, colors, mood, and abstract shapes only. Do NOT include any text or typography. This field must always be in English for DALL-E.
PROMPT;

        $orchestratorResult = $this->chatCompletion($orchestratorSystem, $userMessage);

        if (!$orchestratorResult) {
            return ['success' => false, 'data' => [], 'errors' => ['orchestrator'], 'partial' => false];
        }

        $language = $orchestratorResult['language'] ?? 'en';
        $tone = $orchestratorResult['tone'] ?? 'professional';
        $summary = $orchestratorResult['content_summary'] ?? '';
        $keyTopics = $orchestratorResult['key_topics'] ?? [];

        // Build context block for parallel calls
        $contextBlock = "DETECTED LANGUAGE: {$language} (you MUST write ALL output in this language)\n";
        $contextBlock .= "WRITING TONE: {$tone}\n";
        $contextBlock .= "CONTENT SUMMARY: {$summary}\n";
        $contextBlock .= "KEY TOPICS: " . implode(', ', $keyTopics) . "\n\n";
        $contextBlock .= "PDF CONTENT:\n" . $pdfText;
        if (!empty($context)) {
            $contextBlock .= "\n\nADDITIONAL CONTEXT FROM AUTHOR:\n" . $context;
        }

        // Step 2: Three parallel specialized calls
        $parallelRequests = [
            [
                'key' => 'hero_email',
                'system' => <<<PROMPT
You are a marketing copywriter. Generate hero section copy and email delivery content for a lead magnet landing page.

CRITICAL: Write ALL output in the language specified (see DETECTED LANGUAGE). Never switch to English.

Return a JSON object with exactly these fields:
- "variants": an array of exactly 3 hero variant objects. Each variant has:
  - "hero_headline": an attention-grabbing headline (max 10 words, punchy and benefit-driven)
  - "hero_headline_accent": the 2-3 most impactful/important words from that headline (must be a substring of hero_headline)
  - "hero_subheadline": supporting text below the headline (1-2 sentences, explains the value)
  - "hero_cta_text": call-to-action button text (2-4 words)
  - "hero_badge": a short context/urgency label (max 5 words, e.g. "Free Guide", "Exclusive PDF", "New Ebook")
  Variant 1: Bold & direct style. Variant 2: Curiosity-driven style. Variant 3: Benefit-focused style.
- "email_subject": email subject line for delivering the PDF (friendly, enticing)
- "email_body_html": a short, friendly HTML email body that delivers the download link. Use {{name}} for the recipient's name and {{download_link}} for the PDF download URL. Keep it concise (3-5 short paragraphs). Use simple HTML (p tags, a tag for the link). Make it warm and {$tone}.
PROMPT,
                'user' => $contextBlock,
                'max_tokens' => 3000,
            ],
            [
                'key' => 'content_sections',
                'system' => <<<'PROMPT'
You are a content structure expert. Extract structured content sections from the PDF for a landing page.

CRITICAL: Write ALL output in the language specified (see DETECTED LANGUAGE). Never switch to English.

Return a JSON object with exactly these fields:
- "features_headline": a headline for the features/benefits section (e.g. "What You'll Learn")
- "features": array of 3-6 objects, each with "title" (short, 3-6 words) and "description" (1 sentence). These highlight key takeaways from the PDF.
- "chapters": array of up to 8 objects, each with "number" (integer), "title" (chapter/section title), and "description" (1 sentence summary). This represents the table of contents or main sections of the PDF. If the PDF doesn't have clear chapters, create logical sections based on the content.
- "key_statistics": array of 3-4 objects, each with "value" (a number, percentage, or short metric), "label" (what the number represents), and "icon" (a single relevant emoji). Extract real numbers/data from the PDF where possible, or create compelling statistics about the content.
PROMPT,
                'user' => $contextBlock,
                'max_tokens' => 3000,
            ],
            [
                'key' => 'trust_sections',
                'system' => <<<'PROMPT'
You are a conversion optimization expert. Generate trust-building and audience-targeting content for a lead magnet landing page.

CRITICAL: Write ALL output in the language specified (see DETECTED LANGUAGE). Never switch to English.

Return a JSON object with exactly these fields:
- "target_audience": array of exactly 3 objects, each with "icon" (a single relevant emoji), "title" (short persona name, 3-5 words), and "description" (1 sentence explaining why this persona benefits from the PDF)
- "faq": array of 4-5 objects, each with "question" and "answer". Common questions a prospect might have before downloading. Keep answers concise (1-2 sentences).
- "before_after": an object with "before" (array of 3 pain point strings that the reader experiences without this knowledge) and "after" (array of 3 positive outcome strings after reading the PDF). Keep each string to 1 short sentence.
- "author_bio_suggestion": a 2-3 sentence author bio based on the expertise demonstrated in the PDF. Write it in third person.
- "testimonial_templates": array of 2-3 objects, each with "quote" (a realistic testimonial quote), "name" (a realistic first name), and "title" (a job title). These are editable templates for the user to customize with real feedback.
- "social_proof": array of 3 objects, each with "value" (a compelling metric like "50+" or "10,000+"), "label" (what it represents, e.g. "Pages of Insights"), and "icon" (a single emoji). These replace the default metrics bar on the landing page.
PROMPT,
                'user' => $contextBlock,
                'max_tokens' => 3000,
            ],
        ];

        $parallelResults = $this->parallelChatCompletions($parallelRequests, 90);

        // Step 3: Merge results
        $merged = [
            // From orchestrator
            'language' => $language,
            'title' => $orchestratorResult['title'] ?? '',
            'slug' => $orchestratorResult['slug'] ?? '',
            'subtitle' => $orchestratorResult['subtitle'] ?? '',
            'meta_description' => $orchestratorResult['meta_description'] ?? '',
            'hero_bg_color' => $orchestratorResult['hero_bg_color'] ?? '#1e3a5f',
            'cover_prompt' => $orchestratorResult['cover_prompt'] ?? '',
        ];

        $failedKeys = [];

        // Merge hero_email
        if ($parallelResults['hero_email']) {
            $he = $parallelResults['hero_email'];
            $merged['hero_variants'] = $he['variants'] ?? [];
            $firstVariant = $merged['hero_variants'][0] ?? [];
            $merged['hero_headline'] = $firstVariant['hero_headline'] ?? '';
            $merged['hero_subheadline'] = $firstVariant['hero_subheadline'] ?? '';
            $merged['hero_cta_text'] = $firstVariant['hero_cta_text'] ?? '';
            $merged['hero_badge'] = $firstVariant['hero_badge'] ?? '';
            $merged['hero_headline_accent'] = $firstVariant['hero_headline_accent'] ?? '';
            $merged['email_subject'] = $he['email_subject'] ?? '';
            $merged['email_body_html'] = $he['email_body_html'] ?? '';
        } else {
            $failedKeys[] = 'hero_email';
        }

        // Merge content_sections
        if ($parallelResults['content_sections']) {
            $cs = $parallelResults['content_sections'];
            $merged['features_headline'] = $cs['features_headline'] ?? '';
            $merged['features'] = $cs['features'] ?? [];
            $merged['chapters'] = $cs['chapters'] ?? [];
            $merged['key_statistics'] = $cs['key_statistics'] ?? [];
        } else {
            $failedKeys[] = 'content_sections';
        }

        // Merge trust_sections
        if ($parallelResults['trust_sections']) {
            $ts = $parallelResults['trust_sections'];
            $merged['target_audience'] = $ts['target_audience'] ?? [];
            $merged['faq'] = $ts['faq'] ?? [];
            $merged['before_after'] = $ts['before_after'] ?? ['before' => [], 'after' => []];
            $merged['author_bio'] = $ts['author_bio_suggestion'] ?? '';
            $merged['testimonial_templates'] = $ts['testimonial_templates'] ?? [];
            $merged['social_proof'] = $ts['social_proof'] ?? [];
        } else {
            $failedKeys[] = 'trust_sections';
        }

        return [
            'success' => true,
            'data' => $merged,
            'errors' => $failedKeys,
            'partial' => !empty($failedKeys),
        ];
    }

    /**
     * Generate multiple images in parallel using DALL-E and curl_multi.
     *
     * @param string $prompt Base image description
     * @param int $count Number of images (each as a separate API call with slight variation)
     * @param string $size Image size
     * @return array Array of temporary URLs (nulls for failures)
     */
    public function generateImages(string $prompt, int $count = 3, string $size = '1024x1792'): array {
        $multiHandle = curl_multi_init();
        $handles = [];

        for ($i = 0; $i < $count; $i++) {
            $payload = [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
                'quality' => 'standard',
            ];

            $ch = $this->buildCurlHandle('POST', '/images/generations', $payload, 120);
            $handles[$i] = $ch;
            curl_multi_add_handle($multiHandle, $ch);
        }

        $running = null;
        do {
            $status = curl_multi_exec($multiHandle, $running);
            if ($running > 0) {
                curl_multi_select($multiHandle, 1);
            }
        } while ($running > 0 && $status === CURLM_OK);

        $results = [];
        foreach ($handles as $i => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $url = null;

            if ($response && $httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                $url = $responseData['data'][0]['url'] ?? null;
            } else {
                error_log("OpenAI DALL-E parallel call {$i} failed: HTTP {$httpCode}");
            }

            $results[$i] = $url;
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiHandle);
        return $results;
    }

    /**
     * Build a cURL handle for an OpenAI API request (un-executed).
     */
    private function buildCurlHandle(string $method, string $endpoint, ?array $data = null, int $timeout = 120) {
        $url = $this->apiBase . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        return $ch;
    }

    private function makeRequest(string $method, string $endpoint, ?array $data = null): array {
        $ch = $this->buildCurlHandle($method, $endpoint, $data);

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
