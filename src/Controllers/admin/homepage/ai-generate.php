<?php

use App\Services\OpenAIService;
use App\Models\Product;
use App\Models\Article;
use App\Models\Course;
use App\Models\Ebook;

header('Content-Type: application/json');

if (!isPost()) {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request body']);
    exit;
}

// CSRF verification
if (!verifyCsrfToken($input[CSRF_TOKEN_NAME] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if (!OpenAIService::isConfigured()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'AI is not configured. Please contact support.']);
    exit;
}

$prompt = trim($input['prompt'] ?? '');
if (empty($prompt) || strlen($prompt) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please provide a more detailed description (at least 10 characters).']);
    exit;
}

if (strlen($prompt) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Description is too long (max 2000 characters).']);
    exit;
}

// Gather tenant context
$tenant = currentTenant();
$tenantId = currentTenantId();

$features = [];
if (tenantFeature('blog')) $features[] = 'blog (articles)';
if (tenantFeature('ebooks')) $features[] = 'ebooks';
if (tenantFeature('courses')) $features[] = 'courses';
if (tenantFeature('orders')) $features[] = 'products/shop';
if (tenantFeature('lead_magnets')) $features[] = 'lead magnets';
if (tenantFeature('newsletters')) $features[] = 'newsletter';

// Get existing content names for context
$contentContext = [];
if (tenantFeature('orders')) {
    $products = Product::publishedByTenant($tenantId, 10);
    if (!empty($products)) {
        $contentContext[] = 'Products: ' . implode(', ', array_column($products, 'name'));
    }
}
if (tenantFeature('blog')) {
    $articles = Article::publishedByTenant($tenantId, 10);
    if (!empty($articles)) {
        $contentContext[] = 'Articles: ' . implode(', ', array_column($articles, 'title'));
    }
}
if (tenantFeature('courses')) {
    $courses = Course::publishedByTenant($tenantId, 10);
    if (!empty($courses)) {
        $contentContext[] = 'Courses: ' . implode(', ', array_column($courses, 'name'));
    }
}
if (tenantFeature('ebooks')) {
    $ebooks = Ebook::publishedByTenant($tenantId);
    if (!empty($ebooks)) {
        $contentContext[] = 'Ebooks: ' . implode(', ', array_column($ebooks, 'title'));
    }
}

// Build system prompt
$systemPrompt = <<<'SYSTEMPROMPT'
You are a homepage design assistant for a SaaS platform. The user will describe their business and what they want for their homepage. You must generate a complete homepage configuration as JSON.

## Available Templates
- "starter" — Clean, centered layout. The classic default.
- "bold" — Gradient hero, modern SaaS aesthetic. Includes trust strip.
- "elegant" — Split hero, refined editorial feel.

## Available Section Types
- "trust_strip" — Shows content count statistics. Best paired with the Bold template. No subtitle or count fields.
- "articles" — Displays blog articles. Has heading, subtitle, count (1-12).
- "ebooks" — Displays ebooks. Has heading, subtitle, count (1-12).
- "products" — Displays products. Has heading, subtitle, count (1-12).
- "courses" — Displays courses. Has heading, subtitle, count (1-12).
- "newsletter" — Newsletter signup form. Has heading, subtitle.
- "richtext" — Free-form HTML content block. Has heading and body (HTML). Good for "About Us", "Why Choose Us", testimonials, etc.

## Rules
1. ALL text must be in English.
2. Only include section types for features the tenant has enabled (see ENABLED FEATURES below).
3. If the Bold template is chosen, always include a trust_strip as the first section.
4. Include 3-6 sections total — don't overload the page.
5. For content sections (articles, ebooks, products, courses), default count to 3 unless the user specifies otherwise.
6. The tagline should be short and punchy (max 60 chars).
7. The hero_subtitle should be 1-2 sentences expanding on the tagline.
8. CTA URLs must use these exact paths: /produkter (products), /blog (articles), /eboger (ebooks), /kurser (courses), /lp (lead magnets). For custom pages, use /.
9. Richtext body should be clean HTML with <h2>, <h3>, <p>, <ul>, <li> tags. Keep it concise.
10. Make the content feel professional, specific to the business, and conversion-oriented.

## Response Format
Return ONLY a JSON object with this exact structure:
{
  "template": "starter|bold|elegant",
  "tagline": "Short tagline for the hero",
  "hero_subtitle": "1-2 sentence subtitle for the hero section",
  "hero": {
    "cta_primary_text": "Primary Button Text",
    "cta_primary_url": "/produkter",
    "cta_secondary_text": "Secondary Button Text",
    "cta_secondary_url": "/blog"
  },
  "sections": [
    { "type": "trust_strip", "enabled": true, "heading": "" },
    { "type": "products", "enabled": true, "heading": "Our Products", "subtitle": "Browse our collection", "count": 3 },
    { "type": "richtext", "enabled": true, "heading": "About Us", "body": "<p>HTML content here...</p>" },
    { "type": "newsletter", "enabled": true, "heading": "Stay Updated", "subtitle": "Get the latest updates" }
  ]
}
SYSTEMPROMPT;

// Build user message with tenant context
$userMessage = "## Business Description\n{$prompt}\n\n";
$userMessage .= "## Tenant Context\n";
$userMessage .= "Company Name: " . ($tenant['company_name'] ?? 'Not set') . "\n";

if (!empty($tenant['tagline'])) {
    $userMessage .= "Current Tagline: {$tenant['tagline']}\n";
}
if (!empty($tenant['hero_subtitle'])) {
    $userMessage .= "Current Hero Subtitle: {$tenant['hero_subtitle']}\n";
}

$userMessage .= "Enabled Features: " . (empty($features) ? 'None' : implode(', ', $features)) . "\n";

if (!empty($contentContext)) {
    $userMessage .= "\n## Existing Content\n" . implode("\n", $contentContext) . "\n";
}

$userMessage .= "\nGenerate a complete homepage configuration based on the business description above.";

// Call OpenAI
$openai = new OpenAIService();
$result = $openai->chatCompletion($systemPrompt, $userMessage);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'AI generation failed. Please try again.']);
    exit;
}

// Validate required fields exist
$requiredKeys = ['template', 'tagline', 'hero_subtitle', 'hero', 'sections'];
foreach ($requiredKeys as $key) {
    if (!isset($result[$key])) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'AI returned incomplete data. Please try again.']);
        exit;
    }
}

// Validate template
if (!in_array($result['template'], ['starter', 'bold', 'elegant'])) {
    $result['template'] = 'starter';
}

// Validate sections have required fields
$allowedTypes = ['articles', 'ebooks', 'products', 'courses', 'newsletter', 'richtext', 'trust_strip'];
$validSections = [];
foreach ($result['sections'] as $i => $sec) {
    if (!isset($sec['type']) || !in_array($sec['type'], $allowedTypes)) continue;

    $validSection = [
        'id' => 'sec_' . bin2hex(random_bytes(4)),
        'type' => $sec['type'],
        'enabled' => $sec['enabled'] ?? true,
        'heading' => $sec['heading'] ?? '',
    ];

    if (isset($sec['subtitle'])) $validSection['subtitle'] = $sec['subtitle'];
    if (isset($sec['count'])) $validSection['count'] = max(1, min(12, (int)$sec['count']));
    if ($sec['type'] === 'richtext' && isset($sec['body'])) $validSection['body'] = $sec['body'];

    $validSections[] = $validSection;
}
$result['sections'] = $validSections;

echo json_encode(['success' => true, 'data' => $result]);
