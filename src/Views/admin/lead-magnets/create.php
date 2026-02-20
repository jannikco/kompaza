<?php
$pageTitle = 'Create Lead Magnet';
$currentPage = 'lead-magnets';
$tenant = currentTenant();
$aiConfigured = \App\Services\OpenAIService::isConfigured();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/lead-magnets" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Lead Magnets
    </a>
</div>

<div x-data="leadMagnetWizard()" x-cloak>

    <!-- Step 1: Upload PDF for AI generation -->
    <div x-show="step === 1" x-transition>
        <div class="max-w-2xl mx-auto">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-8 text-center">
                <div class="w-16 h-16 bg-indigo-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Create with AI</h2>
                <p class="text-gray-400 mb-8">Upload your PDF and let AI generate the landing page content, email copy, and more.</p>

                <div x-show="!loading">
                    <label class="block mb-6">
                        <div class="relative border-2 border-dashed border-gray-600 rounded-xl p-8 hover:border-indigo-500 transition cursor-pointer"
                             :class="pdfFile ? 'border-indigo-500 bg-indigo-500/5' : ''">
                            <input type="file" accept=".pdf" @change="pdfFile = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div x-show="!pdfFile" class="text-center">
                                <svg class="w-10 h-10 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-gray-400">Click to select a PDF file</p>
                            </div>
                            <div x-show="pdfFile" class="flex items-center justify-center space-x-3">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-white font-medium" x-text="pdfFile?.name"></span>
                            </div>
                        </div>
                    </label>

                    <template x-if="error">
                        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm" x-text="error"></div>
                    </template>

                    <div class="flex items-center justify-center space-x-4">
                        <button @click="generateWithAI()" :disabled="!pdfFile"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition inline-flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Generate with AI</span>
                        </button>
                        <button @click="step = 2" class="text-sm text-gray-400 hover:text-white transition">
                            Skip AI &rarr;
                        </button>
                    </div>
                </div>

                <!-- Loading state -->
                <div x-show="loading" class="py-8">
                    <div class="flex items-center justify-center space-x-3 mb-4">
                        <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-white font-medium">AI is analyzing your PDF...</span>
                    </div>
                    <p class="text-gray-500 text-sm">This may take 15-30 seconds</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Full form (pre-filled by AI or blank) -->
    <div x-show="step === 2" x-transition>
        <template x-if="aiGenerated">
            <div class="mb-6 p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-start space-x-3">
                <svg class="w-5 h-5 text-indigo-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <div>
                    <p class="text-indigo-300 font-medium">AI-generated content</p>
                    <p class="text-gray-400 text-sm">Review and edit the fields below before saving.</p>
                </div>
            </div>
        </template>

        <form method="POST" action="/admin/lead-magnets/gem" enctype="multipart/form-data" class="space-y-8">
            <?= csrfField() ?>

            <!-- Hidden fields for pre-uploaded PDF -->
            <input type="hidden" name="pdf_filename_existing" :value="pdfFilename">
            <input type="hidden" name="pdf_original_name_existing" :value="pdfOriginalName">

            <!-- Basic Information -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title</label>
                        <input type="text" name="title" id="title" required x-model="formData.title"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., 10 Tips for Better Marketing">
                    </div>
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
                        <input type="text" name="slug" id="slug" required x-model="formData.slug"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="10-tips-for-better-marketing">
                    </div>
                    <div class="md:col-span-2">
                        <label for="subtitle" class="block text-sm font-medium text-gray-300 mb-2">Subtitle</label>
                        <input type="text" name="subtitle" id="subtitle" x-model="formData.subtitle"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="A short subtitle for the lead magnet">
                    </div>
                    <div class="md:col-span-2">
                        <label for="meta_description" class="block text-sm font-medium text-gray-300 mb-2">Meta Description</label>
                        <input type="text" name="meta_description" id="meta_description" maxlength="160" x-model="formData.meta_description"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="SEO description (max 160 characters)">
                    </div>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Hero Section</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="hero_headline" class="block text-sm font-medium text-gray-300 mb-2">Hero Headline</label>
                        <input type="text" name="hero_headline" id="hero_headline" x-model="formData.hero_headline"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Main headline on the landing page">
                    </div>
                    <div class="md:col-span-2">
                        <label for="hero_subheadline" class="block text-sm font-medium text-gray-300 mb-2">Hero Subheadline</label>
                        <input type="text" name="hero_subheadline" id="hero_subheadline" x-model="formData.hero_subheadline"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Supporting text below the headline">
                    </div>
                    <div>
                        <label for="hero_cta_text" class="block text-sm font-medium text-gray-300 mb-2">Hero CTA Text</label>
                        <input type="text" name="hero_cta_text" id="hero_cta_text" x-model="formData.hero_cta_text"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Download Free Guide">
                    </div>
                    <div>
                        <label for="hero_bg_color" class="block text-sm font-medium text-gray-300 mb-2">Hero Background Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="hero_bg_color" id="hero_bg_color" x-model="formData.hero_bg_color"
                                class="w-12 h-10 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                            <input type="text" x-model="formData.hero_bg_color"
                                class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="#1e1b4b">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="hero_image" class="block text-sm font-medium text-gray-300 mb-2">Hero Image</label>
                        <input type="file" name="hero_image" id="hero_image" accept="image/*"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Features Section</h3>
                <div class="space-y-6">
                    <div>
                        <label for="features_headline" class="block text-sm font-medium text-gray-300 mb-2">Features Headline</label>
                        <input type="text" name="features_headline" id="features_headline" x-model="formData.features_headline"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., What You'll Learn">
                    </div>

                    <!-- Dynamic features -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Features</label>
                        <div class="space-y-3">
                            <template x-for="(feature, index) in features" :key="index">
                                <div class="bg-gray-700/50 border border-gray-600 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1 space-y-2">
                                            <input type="text" x-model="feature.title" :name="'features['+index+'][title]'"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                placeholder="Feature title">
                                            <input type="text" x-model="feature.description" :name="'features['+index+'][description]'"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                placeholder="Feature description">
                                        </div>
                                        <button type="button" @click="features.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-400 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="features.push({title: '', description: ''})"
                            class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-400 hover:text-indigo-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add feature</span>
                        </button>
                        <!-- Serialized features JSON for the backend -->
                        <input type="hidden" name="features" :value="JSON.stringify(features)">
                    </div>
                </div>
            </div>

            <!-- PDF File -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Downloadable PDF</h3>
                <div>
                    <template x-if="pdfFilename">
                        <div class="flex items-center space-x-3 mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-green-300 text-sm">PDF uploaded: <span x-text="pdfOriginalName" class="font-medium"></span></span>
                        </div>
                    </template>
                    <template x-if="!pdfFilename">
                        <div>
                            <label for="pdf_file" class="block text-sm font-medium text-gray-300 mb-2">PDF File</label>
                            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                            <p class="text-xs text-gray-500 mt-2">Upload the PDF file that will be delivered after signup.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Email Delivery</h3>
                <div class="space-y-6">
                    <div>
                        <label for="email_subject" class="block text-sm font-medium text-gray-300 mb-2">Email Subject</label>
                        <input type="text" name="email_subject" id="email_subject" x-model="formData.email_subject"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Here's your free guide!">
                    </div>
                    <div>
                        <label for="email_body_html" class="block text-sm font-medium text-gray-300 mb-2">Email Body</label>
                        <textarea name="email_body_html" id="email_body_html" rows="8" x-model="formData.email_body_html"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="The email content that will be sent with the download link..."></textarea>
                        <p class="text-xs text-gray-500 mt-2">Use {{download_link}} for the PDF link. Use {{name}} for the recipient's name.</p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Publishing</h3>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                    <select name="status" id="status"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-4">
                <a href="/admin/lead-magnets" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                    Create Lead Magnet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function leadMagnetWizard() {
    return {
        step: <?= $aiConfigured ? '1' : '2' ?>,
        loading: false,
        error: '',
        pdfFile: null,
        pdfFilename: '',
        pdfOriginalName: '',
        aiGenerated: false,
        features: [],
        formData: {
            title: '',
            slug: '',
            subtitle: '',
            meta_description: '',
            hero_headline: '',
            hero_subheadline: '',
            hero_cta_text: '',
            hero_bg_color: '#1e1b4b',
            features_headline: '',
            email_subject: '',
            email_body_html: '',
        },

        async generateWithAI() {
            if (!this.pdfFile) return;
            this.loading = true;
            this.error = '';

            const formData = new FormData();
            formData.append('pdf_file', this.pdfFile);
            formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= generateCsrfToken() ?>');

            try {
                const response = await fetch('/admin/lead-magnets/ai-generate', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (!result.success) {
                    this.error = result.error || 'Something went wrong. Please try again.';
                    this.loading = false;
                    return;
                }

                this.pdfFilename = result.pdf_filename || '';
                this.pdfOriginalName = result.pdf_original_name || '';

                if (result.ai_generated && result.data) {
                    this.aiGenerated = true;
                    const d = result.data;
                    this.formData.title = d.title || '';
                    this.formData.slug = d.slug || '';
                    this.formData.subtitle = d.subtitle || '';
                    this.formData.meta_description = d.meta_description || '';
                    this.formData.hero_headline = d.hero_headline || '';
                    this.formData.hero_subheadline = d.hero_subheadline || '';
                    this.formData.hero_cta_text = d.hero_cta_text || '';
                    this.formData.hero_bg_color = d.hero_bg_color || '#1e1b4b';
                    this.formData.features_headline = d.features_headline || '';
                    this.formData.email_subject = d.email_subject || '';
                    this.formData.email_body_html = d.email_body_html || '';

                    if (d.features && Array.isArray(d.features)) {
                        this.features = d.features;
                    }
                }

                this.step = 2;
            } catch (e) {
                this.error = 'Network error. Please try again.';
            }

            this.loading = false;
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
