<?php
$pageTitle = 'Edit Lead Magnet';
$currentPage = 'lead-magnets';
$tenant = currentTenant();

$existingTargetAudience = [];
if (!empty($leadMagnet['target_audience'])) {
    $existingTargetAudience = json_decode($leadMagnet['target_audience'], true) ?: [];
}

$existingFaq = [];
if (!empty($leadMagnet['faq'])) {
    $existingFaq = json_decode($leadMagnet['faq'], true) ?: [];
}

$existingFeatures = [];
if (!empty($leadMagnet['features'])) {
    $existingFeatures = json_decode($leadMagnet['features'], true) ?: [];
}

$existingChapters = [];
if (!empty($leadMagnet['chapters'])) {
    $existingChapters = json_decode($leadMagnet['chapters'], true) ?: [];
}

$existingKeyStatistics = [];
if (!empty($leadMagnet['key_statistics'])) {
    $existingKeyStatistics = json_decode($leadMagnet['key_statistics'], true) ?: [];
}

$existingBeforeAfter = ['before' => [], 'after' => []];
if (!empty($leadMagnet['before_after'])) {
    $existingBeforeAfter = json_decode($leadMagnet['before_after'], true) ?: ['before' => [], 'after' => []];
}

$existingTestimonialTemplates = [];
if (!empty($leadMagnet['testimonial_templates'])) {
    $existingTestimonialTemplates = json_decode($leadMagnet['testimonial_templates'], true) ?: [];
}

$existingSocialProof = [];
if (!empty($leadMagnet['social_proof'])) {
    $existingSocialProof = json_decode($leadMagnet['social_proof'], true) ?: [];
}

ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <a href="/admin/lead-magnets" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Lead Magnets
    </a>
    <a href="/lp/<?= h($leadMagnet['slug']) ?>" target="_blank" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500 transition">
        View Page
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
    </a>
</div>

<div x-data="{
    features: <?= h(json_encode($existingFeatures)) ?>,
    chapters: <?= h(json_encode($existingChapters)) ?>,
    keyStatistics: <?= h(json_encode($existingKeyStatistics)) ?>,
    targetAudience: <?= h(json_encode($existingTargetAudience)) ?>,
    faq: <?= h(json_encode($existingFaq)) ?>,
    beforeAfter: <?= h(json_encode($existingBeforeAfter)) ?>,
    authorBio: <?= h(json_encode($leadMagnet['author_bio'] ?? '')) ?>,
    testimonialTemplates: <?= h(json_encode($existingTestimonialTemplates)) ?>,
    socialProof: <?= h(json_encode($existingSocialProof)) ?>,
    selectedTemplate: <?= h(json_encode($leadMagnet['template'] ?? 'bold')) ?>
}">
<form method="POST" action="/admin/lead-magnets/opdater" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $leadMagnet['id'] ?>">

    <!-- Hidden JSON fields -->
    <input type="hidden" name="target_audience" :value="JSON.stringify(targetAudience)">
    <input type="hidden" name="faq" :value="JSON.stringify(faq)">
    <input type="hidden" name="chapters" :value="JSON.stringify(chapters)">
    <input type="hidden" name="key_statistics" :value="JSON.stringify(keyStatistics)">
    <input type="hidden" name="before_after" :value="JSON.stringify(beforeAfter)">
    <input type="hidden" name="testimonial_templates" :value="JSON.stringify(testimonialTemplates)">
    <input type="hidden" name="social_proof" :value="JSON.stringify(socialProof)">

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" name="title" id="title" required
                    value="<?= h($leadMagnet['title']) ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., 10 Tips for Better Marketing">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                <input type="text" name="slug" id="slug" required
                    value="<?= h($leadMagnet['slug']) ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="10-tips-for-better-marketing">
            </div>
            <div class="md:col-span-2">
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                <input type="text" name="subtitle" id="subtitle"
                    value="<?= h($leadMagnet['subtitle'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="A short subtitle for the lead magnet">
            </div>
            <div class="md:col-span-2">
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" maxlength="160"
                    value="<?= h($leadMagnet['meta_description'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="SEO description (max 160 characters)">
            </div>
        </div>
    </div>

    <!-- Landing Page Template -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Landing Page Template</h3>
        <p class="text-gray-500 text-sm mb-4">Choose the design style for this lead magnet's landing page.</p>
        <input type="hidden" name="template" :value="selectedTemplate">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <!-- Bold -->
            <div @click="selectedTemplate = 'bold'"
                 class="border-2 rounded-lg p-2.5 cursor-pointer transition hover:shadow-md text-center"
                 :class="selectedTemplate === 'bold' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                <div class="w-full aspect-[3/4] rounded mb-2 overflow-hidden relative" style="background: linear-gradient(135deg, #4f46e5, #1e1b4b)">
                    <div class="absolute top-2 left-2 right-2 h-1.5 bg-white/30 rounded"></div>
                    <div class="absolute top-5 left-2 w-8 h-1 bg-white/50 rounded"></div>
                    <div class="absolute bottom-6 left-2 right-2 grid grid-cols-3 gap-0.5">
                        <div class="h-3 bg-white/15 rounded"></div>
                        <div class="h-3 bg-white/15 rounded"></div>
                        <div class="h-3 bg-white/15 rounded"></div>
                    </div>
                    <svg class="absolute bottom-0 w-full" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 10 Q25 2 50 7 Q75 0 100 10 Z" fill="rgba(255,255,255,0.1)"/></svg>
                </div>
                <p class="font-medium text-gray-900 text-xs">Bold</p>
            </div>
            <!-- Minimal -->
            <div @click="selectedTemplate = 'minimal'"
                 class="border-2 rounded-lg p-2.5 cursor-pointer transition hover:shadow-md text-center"
                 :class="selectedTemplate === 'minimal' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                <div class="w-full aspect-[3/4] rounded mb-2 overflow-hidden relative bg-white border border-gray-200">
                    <div class="absolute top-2 left-2 right-2 h-1.5 bg-gray-100 rounded"></div>
                    <div class="absolute top-5 left-2 w-10 h-1 bg-gray-200 rounded"></div>
                    <div class="absolute top-9 left-2 right-2 space-y-1.5">
                        <div class="h-2 bg-gray-50 rounded"></div>
                        <div class="h-2 bg-gray-50 rounded"></div>
                    </div>
                    <div class="absolute bottom-2 left-2 right-2 h-2 bg-gray-100 rounded"></div>
                </div>
                <p class="font-medium text-gray-900 text-xs">Minimal</p>
            </div>
            <!-- Classic -->
            <div @click="selectedTemplate = 'classic'"
                 class="border-2 rounded-lg p-2.5 cursor-pointer transition hover:shadow-md text-center"
                 :class="selectedTemplate === 'classic' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                <div class="w-full aspect-[3/4] rounded mb-2 overflow-hidden relative" style="background: #faf7f2">
                    <div class="absolute top-2 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-amber-900/20 rounded"></div>
                    <div class="absolute top-5 left-3 right-3 h-px bg-amber-900/10"></div>
                    <div class="absolute top-7 left-3 right-3 space-y-1">
                        <div class="h-1 bg-amber-900/10 rounded"></div>
                        <div class="h-1 bg-amber-900/10 rounded w-4/5"></div>
                    </div>
                    <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-10 h-2 bg-amber-900/15 rounded"></div>
                </div>
                <p class="font-medium text-gray-900 text-xs">Classic</p>
            </div>
            <!-- Split -->
            <div @click="selectedTemplate = 'split'"
                 class="border-2 rounded-lg p-2.5 cursor-pointer transition hover:shadow-md text-center"
                 :class="selectedTemplate === 'split' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                <div class="w-full aspect-[3/4] rounded mb-2 overflow-hidden relative bg-gray-50 border border-gray-200">
                    <div class="absolute top-0 left-0 w-1/2 h-1/2 bg-indigo-100"></div>
                    <div class="absolute top-0 right-0 w-1/2 h-1/2 p-1.5">
                        <div class="h-1 bg-gray-200 rounded mb-0.5"></div>
                        <div class="h-0.5 bg-gray-100 rounded w-4/5"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-1/2 h-1/2 p-1.5">
                        <div class="h-1 bg-gray-200 rounded mb-0.5"></div>
                        <div class="h-0.5 bg-gray-100 rounded w-3/5"></div>
                    </div>
                    <div class="absolute bottom-0 right-0 w-1/2 h-1/2 bg-indigo-50"></div>
                </div>
                <p class="font-medium text-gray-900 text-xs">Split</p>
            </div>
            <!-- Dark -->
            <div @click="selectedTemplate = 'dark'"
                 class="border-2 rounded-lg p-2.5 cursor-pointer transition hover:shadow-md text-center"
                 :class="selectedTemplate === 'dark' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                <div class="w-full aspect-[3/4] rounded mb-2 overflow-hidden relative" style="background: #0f172a">
                    <div class="absolute top-2 left-2 right-2 h-1.5 bg-white/10 rounded"></div>
                    <div class="absolute top-5 left-2 w-8 h-1 bg-indigo-400/30 rounded"></div>
                    <div class="absolute top-10 left-2 right-2 grid grid-cols-2 gap-1">
                        <div class="h-4 bg-white/5 rounded border border-white/10"></div>
                        <div class="h-4 bg-white/5 rounded border border-white/10"></div>
                    </div>
                    <div class="absolute bottom-2 left-2 right-2 h-2 bg-indigo-500/20 rounded border border-indigo-400/20"></div>
                </div>
                <p class="font-medium text-gray-900 text-xs">Dark</p>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Hero Section</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="hero_headline" class="block text-sm font-medium text-gray-700 mb-2">Hero Headline</label>
                <input type="text" name="hero_headline" id="hero_headline"
                    value="<?= h($leadMagnet['hero_headline'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Main headline on the landing page">
            </div>
            <div class="md:col-span-2">
                <label for="hero_subheadline" class="block text-sm font-medium text-gray-700 mb-2">Hero Subheadline</label>
                <input type="text" name="hero_subheadline" id="hero_subheadline"
                    value="<?= h($leadMagnet['hero_subheadline'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Supporting text below the headline">
            </div>
            <div>
                <label for="hero_cta_text" class="block text-sm font-medium text-gray-700 mb-2">Hero CTA Text</label>
                <input type="text" name="hero_cta_text" id="hero_cta_text"
                    value="<?= h($leadMagnet['hero_cta_text'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Download Free Guide">
            </div>
            <div>
                <label for="hero_badge" class="block text-sm font-medium text-gray-700 mb-2">Hero Badge</label>
                <input type="text" name="hero_badge" id="hero_badge"
                    value="<?= h($leadMagnet['hero_badge'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Free Guide">
            </div>
            <div>
                <label for="hero_headline_accent" class="block text-sm font-medium text-gray-700 mb-2">Headline Accent Words</label>
                <input type="text" name="hero_headline_accent" id="hero_headline_accent"
                    value="<?= h($leadMagnet['hero_headline_accent'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Key words to highlight in brand color">
            </div>
            <div>
                <label for="hero_bg_color" class="block text-sm font-medium text-gray-700 mb-2">Hero Background Color</label>
                <div class="flex items-center space-x-3">
                    <input type="color" name="hero_bg_color" id="hero_bg_color" value="<?= h($leadMagnet['hero_bg_color'] ?? '#1e1b4b') ?>"
                        class="w-12 h-10 bg-white border border-gray-300 rounded-lg cursor-pointer">
                    <input type="text" id="hero_bg_color_text" value="<?= h($leadMagnet['hero_bg_color'] ?? '#1e1b4b') ?>"
                        class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="#1e1b4b"
                        oninput="document.getElementById('hero_bg_color').value = this.value"
                        onchange="document.getElementById('hero_bg_color').value = this.value">
                </div>
            </div>
            <div class="md:col-span-2">
                <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                <?php if (!empty($leadMagnet['hero_image_path'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <img src="<?= h(imageUrl($leadMagnet['hero_image_path'])) ?>" alt="Current hero image" class="h-16 w-auto rounded-lg border border-gray-300">
                        <span class="text-sm text-gray-500">Current image</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="hero_image" id="hero_image" accept="image/*"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current image.</p>
            </div>
        </div>
    </div>

    <!-- Cover Image -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Book Cover</h3>
        <p class="text-gray-500 text-sm mb-4">This image is displayed as a 3D book mockup on the landing page.</p>
        <?php if (!empty($leadMagnet['cover_image_path'])): ?>
            <div class="mb-4 flex items-center space-x-4">
                <img src="<?= h(imageUrl($leadMagnet['cover_image_path'])) ?>" alt="Current cover" class="h-32 w-auto rounded-lg border border-gray-300">
                <span class="text-sm text-gray-500">Current cover</span>
            </div>
        <?php endif; ?>
        <input type="file" name="cover_image" accept="image/*"
            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
        <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current cover. Upload a new image to replace it.</p>
    </div>

    <!-- Social Proof Bar -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Social Proof Bar</h3>
        <p class="text-gray-500 text-sm mb-6">Customizable metrics bar shown below the hero. Leave empty to use defaults.</p>
        <div class="space-y-3">
            <template x-for="(proof, index) in socialProof" :key="index">
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-1">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" x-model="proof.icon" maxlength="4"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Icon">
                                <input type="text" x-model="proof.value"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="e.g., 10,000+">
                                <input type="text" x-model="proof.label"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Label">
                            </div>
                        </div>
                        <button type="button" @click="socialProof.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" @click="socialProof.push({value: '', label: '', icon: ''})"
            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add metric</span>
        </button>
    </div>

    <!-- Features -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Features Section</h3>
        <div class="space-y-6">
            <div>
                <label for="features_headline" class="block text-sm font-medium text-gray-700 mb-2">Features Headline</label>
                <input type="text" name="features_headline" id="features_headline"
                    value="<?= h($leadMagnet['features_headline'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., What You'll Learn">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Features</label>
                <div class="space-y-3">
                    <template x-for="(feature, index) in features" :key="index">
                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-1 space-y-2">
                                    <input type="text" x-model="feature.title"
                                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Feature title">
                                    <input type="text" x-model="feature.description"
                                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Feature description">
                                </div>
                                <button type="button" @click="features.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="features.push({title: '', description: ''})"
                    class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add feature</span>
                </button>
                <input type="hidden" name="features" :value="JSON.stringify(features)">
            </div>
        </div>
    </div>

    <!-- Chapters -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Chapters / Table of Contents</h3>
        <p class="text-gray-500 text-sm mb-6">Displayed as a numbered list on the landing page.</p>
        <div class="space-y-3">
            <template x-for="(chapter, index) in chapters" :key="index">
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 bg-indigo-500/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-indigo-600 font-bold text-sm" x-text="chapter.number || (index + 1)"></span>
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="text" x-model="chapter.title"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Chapter title">
                            <input type="text" x-model="chapter.description"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Brief description">
                        </div>
                        <button type="button" @click="chapters.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" @click="chapters.push({number: chapters.length + 1, title: '', description: ''})"
            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add chapter</span>
        </button>
    </div>

    <!-- Key Statistics -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Key Statistics</h3>
        <p class="text-gray-500 text-sm mb-6">Bold stat cards displayed on the landing page.</p>
        <div class="space-y-3">
            <template x-for="(stat, index) in keyStatistics" :key="index">
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-1">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" x-model="stat.icon" maxlength="4"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Icon">
                                <input type="text" x-model="stat.value"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="e.g., 50+">
                                <input type="text" x-model="stat.label"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Label">
                            </div>
                        </div>
                        <button type="button" @click="keyStatistics.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" @click="keyStatistics.push({value: '', label: '', icon: ''})"
            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add statistic</span>
        </button>
    </div>

    <!-- Target Audience -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Target Audience</h3>
        <p class="text-gray-500 text-sm mb-4">Define who this lead magnet is for. These appear as persona cards on the landing page.</p>
        <div class="space-y-3">
            <template x-for="(persona, index) in targetAudience" :key="index">
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center space-x-2">
                                <input type="text" x-model="persona.icon" maxlength="4"
                                    class="w-16 px-3 py-2 bg-white border border-gray-300 text-gray-900 text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="&#x1F4BC;">
                                <input type="text" x-model="persona.title"
                                    class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Persona title">
                            </div>
                            <input type="text" x-model="persona.description"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Why this persona benefits from the guide">
                        </div>
                        <button type="button" @click="targetAudience.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" @click="targetAudience.push({icon: '', title: '', description: ''})"
            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add persona</span>
        </button>
    </div>

    <!-- Before/After Transformation -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Before/After Transformation</h3>
        <p class="text-gray-500 text-sm mb-6">Show the contrast between current situation and outcome after reading.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-red-600 mb-3">Before (Pain Points)</label>
                <div class="space-y-2">
                    <template x-for="(item, index) in beforeAfter.before" :key="'before-'+index">
                        <div class="flex items-center space-x-2">
                            <span class="text-red-600 flex-shrink-0">&#x2717;</span>
                            <input type="text" x-model="beforeAfter.before[index]"
                                class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Pain point...">
                            <button type="button" @click="beforeAfter.before.splice(index, 1)" class="p-1 text-gray-500 hover:text-red-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="beforeAfter.before.push('')"
                    class="mt-2 text-xs text-red-600 hover:text-red-300 transition">+ Add pain point</button>
            </div>
            <div>
                <label class="block text-sm font-medium text-green-600 mb-3">After (Outcomes)</label>
                <div class="space-y-2">
                    <template x-for="(item, index) in beforeAfter.after" :key="'after-'+index">
                        <div class="flex items-center space-x-2">
                            <span class="text-green-600 flex-shrink-0">&#x2713;</span>
                            <input type="text" x-model="beforeAfter.after[index]"
                                class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Positive outcome...">
                            <button type="button" @click="beforeAfter.after.splice(index, 1)" class="p-1 text-gray-500 hover:text-green-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="beforeAfter.after.push('')"
                    class="mt-2 text-xs text-green-600 hover:text-green-300 transition">+ Add outcome</button>
            </div>
        </div>
    </div>

    <!-- Author Bio -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Author Bio</h3>
        <p class="text-gray-500 text-sm mb-4">A short bio displayed on the landing page. Builds trust and authority.</p>
        <textarea x-model="authorBio" name="author_bio" rows="3"
            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
            placeholder="Write a short author bio..."></textarea>
    </div>

    <!-- Testimonial Templates -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Testimonials</h3>
        <p class="text-gray-500 text-sm mb-6">Edit these to match real feedback from your audience.</p>
        <div class="space-y-3">
            <template x-for="(testimonial, index) in testimonialTemplates" :key="index">
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-1 space-y-2">
                            <textarea x-model="testimonial.quote" rows="2"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Testimonial quote..."></textarea>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="testimonial.name"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Name">
                                <input type="text" x-model="testimonial.title"
                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Job title">
                            </div>
                        </div>
                        <button type="button" @click="testimonialTemplates.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" @click="testimonialTemplates.push({quote: '', name: '', title: ''})"
            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add testimonial</span>
        </button>
    </div>

    <!-- FAQ -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">FAQ</h3>
        <p class="text-gray-500 text-sm mb-4">Common questions prospects might have before downloading. Displayed as an accordion on the landing page.</p>
        <div class="space-y-3">
            <template x-for="(item, index) in faq" :key="index">
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-1 space-y-2">
                            <input type="text" x-model="item.question"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Question">
                            <textarea x-model="item.answer" rows="2"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Answer"></textarea>
                        </div>
                        <button type="button" @click="faq.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" @click="faq.push({question: '', answer: ''})"
            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add FAQ item</span>
        </button>
    </div>

    <!-- PDF File -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Downloadable PDF</h3>
        <div>
            <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">PDF File</label>
            <?php if (!empty($leadMagnet['pdf_filename'])): ?>
                <div class="mb-3 flex items-center space-x-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span class="text-sm text-gray-600"><?= h($leadMagnet['pdf_filename']) ?></span>
                </div>
            <?php endif; ?>
            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
            <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current PDF. Upload a new file to replace it.</p>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Email Delivery</h3>
        <div class="space-y-6">
            <div>
                <label for="email_subject" class="block text-sm font-medium text-gray-700 mb-2">Email Subject</label>
                <input type="text" name="email_subject" id="email_subject"
                    value="<?= h($leadMagnet['email_subject'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Here's your free guide!">
            </div>
            <div>
                <label for="email_body_html" class="block text-sm font-medium text-gray-700 mb-2">Email Body</label>
                <input type="hidden" name="email_body_html" id="email_body_html-hidden" value="<?= h($leadMagnet['email_body_html'] ?? '') ?>">
                <div id="email_body_html-editor" class="bg-white"><?= $leadMagnet['email_body_html'] ?? '' ?></div>
                <p class="text-xs text-gray-500 mt-2">Use {{download_link}} to insert the PDF download link. Use {{name}} to insert the recipient's name.</p>
            </div>
        </div>
    </div>

    <!-- Status -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Publishing</h3>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" id="status"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="draft" <?= ($leadMagnet['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= ($leadMagnet['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/lead-magnets" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Update Lead Magnet
        </button>
    </div>
</form>
</div>

<script>
    // Sync color picker with text input
    document.getElementById('hero_bg_color').addEventListener('input', function() {
        document.getElementById('hero_bg_color_text').value = this.value;
    });

    initRichEditor('email_body_html-editor', 'email_body_html-hidden', { simple: true, height: 300 });
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
