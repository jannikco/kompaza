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

    <!-- Step indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-4">
            <template x-for="(s, i) in [{n:1, label:'Upload PDF'}, {n:2, label:'Book Cover'}, {n:3, label:'Details'}]" :key="i">
                <div class="flex items-center">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition"
                             :class="step >= s.n ? 'bg-indigo-600 text-white' : 'bg-gray-700 text-gray-400'">
                            <template x-if="step > s.n">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="step <= s.n">
                                <span x-text="s.n"></span>
                            </template>
                        </div>
                        <span class="text-sm" :class="step >= s.n ? 'text-white' : 'text-gray-500'" x-text="s.label"></span>
                    </div>
                    <template x-if="i < 2">
                        <div class="w-12 h-px mx-3" :class="step > s.n ? 'bg-indigo-600' : 'bg-gray-700'"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

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
                    <label class="block mb-4">
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

                    <!-- Additional context textarea -->
                    <div class="mb-6 text-left">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Additional context <span class="text-gray-500">(optional)</span></label>
                        <textarea x-model="context" rows="3"
                            class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                            placeholder="Describe your target audience, goals, or anything that helps AI generate better content..."></textarea>
                    </div>

                    <template x-if="error">
                        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm" x-text="error"></div>
                    </template>

                    <div class="flex items-center justify-center space-x-4">
                        <button @click="generateWithAI()" :disabled="!pdfFile"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition inline-flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Generate with AI</span>
                        </button>
                        <button @click="step = 3" class="text-sm text-gray-400 hover:text-white transition">
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

    <!-- Step 2: Book Cover -->
    <div x-show="step === 2" x-transition>
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Book Cover</h2>
                <p class="text-gray-400">Add a cover image for the 3D book mockup on your landing page.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Option 1: Upload a cover -->
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 cursor-pointer hover:border-indigo-500/50 transition"
                     :class="coverMode === 'upload' ? 'border-indigo-500 ring-1 ring-indigo-500/30' : ''"
                     @click="coverMode = 'upload'">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Upload a cover</h3>
                            <p class="text-gray-500 text-sm">Use your own cover image</p>
                        </div>
                    </div>
                    <div x-show="coverMode === 'upload'" x-transition>
                        <label class="block">
                            <div class="relative border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-blue-500 transition cursor-pointer text-center">
                                <input type="file" accept="image/*" @click.stop
                                    @change="uploadCover($event.target.files[0])"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <template x-if="!coverPreview && !coverLoading">
                                    <p class="text-gray-500 text-sm">Click to select an image</p>
                                </template>
                                <template x-if="coverLoading">
                                    <div class="flex items-center justify-center space-x-2 py-2">
                                        <svg class="animate-spin w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-blue-300 text-sm">Uploading...</span>
                                    </div>
                                </template>
                                <template x-if="coverPreview && !coverLoading">
                                    <img :src="coverPreview" class="max-h-48 mx-auto rounded-lg" alt="Cover preview">
                                </template>
                            </div>
                        </label>
                        <template x-if="coverError">
                            <div class="mt-2 p-2 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-xs" x-text="coverError"></div>
                        </template>
                    </div>
                </div>

                <!-- Option 2: Generate with AI -->
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 cursor-pointer hover:border-indigo-500/50 transition"
                     :class="coverMode === 'ai' ? 'border-indigo-500 ring-1 ring-indigo-500/30' : ''"
                     @click="coverMode = 'ai'">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-indigo-500/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Generate with AI</h3>
                            <p class="text-gray-500 text-sm">DALL-E creates a unique cover</p>
                        </div>
                    </div>
                    <div x-show="coverMode === 'ai'" x-transition @click.stop>
                        <textarea x-model="coverPrompt" rows="3"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-3"
                            placeholder="Describe the visual style for your cover..."></textarea>

                        <!-- Generate button -->
                        <div x-show="!coverLoading && !coverPreview">
                            <button type="button" @click="generateCover()"
                                :disabled="!coverPrompt.trim()"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-medium rounded-lg transition text-sm inline-flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Generate Cover</span>
                            </button>
                        </div>

                        <!-- Loading state -->
                        <div x-show="coverLoading" class="text-center py-4">
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <svg class="animate-spin w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-indigo-300 text-sm font-medium">Generating your book cover...</span>
                            </div>
                            <p class="text-gray-500 text-xs">This takes about 15-20 seconds</p>
                        </div>

                        <!-- Preview with Accept/Retry -->
                        <div x-show="coverPreview && !coverLoading">
                            <img :src="coverPreview" class="max-h-48 mx-auto rounded-lg mb-3" alt="AI cover preview">
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="coverPreview = ''; coverImagePath = ''; coverImageUrl = '';"
                                    class="flex-1 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-sm transition">
                                    Retry
                                </button>
                                <span class="flex-1 px-3 py-2 bg-green-600/20 text-green-400 rounded-lg text-sm text-center">
                                    Accepted
                                </span>
                            </div>
                        </div>

                        <template x-if="coverError">
                            <div class="mt-3 p-2 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-xs" x-text="coverError"></div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex items-center justify-between mt-8">
                <button type="button" @click="step = 1" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">
                    &larr; Back
                </button>
                <div class="flex items-center space-x-4">
                    <button type="button" @click="step = 3" class="text-sm text-gray-400 hover:text-white transition">
                        Skip for now &rarr;
                    </button>
                    <button type="button" @click="step = 3"
                        x-show="coverImagePath"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                        Next &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Full form (pre-filled by AI or blank) -->
    <div x-show="step === 3" x-transition>
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

            <!-- Hidden fields for cover image (from AI generation) -->
            <input type="hidden" name="cover_image_path_existing" :value="coverImagePath">

            <!-- Hidden fields for target audience and FAQ as JSON -->
            <input type="hidden" name="target_audience" :value="JSON.stringify(targetAudience)">
            <input type="hidden" name="faq" :value="JSON.stringify(faq)">

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

            <!-- Cover Image Preview (if set in step 2) -->
            <template x-if="coverImagePath">
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Book Cover</h3>
                    <div class="flex items-start space-x-4">
                        <img :src="coverImageUrl" class="h-32 rounded-lg border border-gray-600" alt="Cover preview">
                        <div>
                            <p class="text-gray-400 text-sm">This cover will be displayed as a 3D book mockup on the landing page.</p>
                            <button type="button" @click="step = 2" class="mt-2 text-sm text-indigo-400 hover:text-indigo-300 transition">
                                Change cover
                            </button>
                        </div>
                    </div>
                </div>
            </template>

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
                                            <input type="text" x-model="feature.title"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                placeholder="Feature title">
                                            <input type="text" x-model="feature.description"
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

            <!-- Target Audience -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Target Audience</h3>
                <p class="text-gray-400 text-sm mb-4">Define who this lead magnet is for. These appear as persona cards on the landing page.</p>
                <div class="space-y-3">
                    <template x-for="(persona, index) in targetAudience" :key="index">
                        <div class="bg-gray-700/50 border border-gray-600 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" x-model="persona.icon" maxlength="4"
                                            class="w-16 px-3 py-2 bg-gray-700 border border-gray-600 text-white text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            placeholder="&#x1F4BC;">
                                        <input type="text" x-model="persona.title"
                                            class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            placeholder="Persona title">
                                    </div>
                                    <input type="text" x-model="persona.description"
                                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Why this persona benefits from the guide">
                                </div>
                                <button type="button" @click="targetAudience.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="targetAudience.push({icon: '', title: '', description: ''})"
                    class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-400 hover:text-indigo-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add persona</span>
                </button>
            </div>

            <!-- FAQ -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-6">FAQ</h3>
                <p class="text-gray-400 text-sm mb-4">Common questions prospects might have before downloading. Displayed as an accordion on the landing page.</p>
                <div class="space-y-3">
                    <template x-for="(item, index) in faq" :key="index">
                        <div class="bg-gray-700/50 border border-gray-600 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-1 space-y-2">
                                    <input type="text" x-model="item.question"
                                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Question">
                                    <textarea x-model="item.answer" rows="2"
                                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Answer"></textarea>
                                </div>
                                <button type="button" @click="faq.splice(index, 1)" class="p-1.5 text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="faq.push({question: '', answer: ''})"
                    class="mt-3 inline-flex items-center space-x-1 text-sm text-indigo-400 hover:text-indigo-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add FAQ item</span>
                </button>
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
            <div class="flex items-center justify-between">
                <button type="button" @click="step = 2" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition">
                    &larr; Back to Cover
                </button>
                <div class="flex items-center space-x-4">
                    <a href="/admin/lead-magnets" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        Create Lead Magnet
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function leadMagnetWizard() {
    return {
        step: <?= $aiConfigured ? '1' : '3' ?>,
        loading: false,
        error: '',
        context: '',
        pdfFile: null,
        pdfFilename: '',
        pdfOriginalName: '',
        aiGenerated: false,
        features: [],
        targetAudience: [],
        faq: [],

        // Cover state
        coverMode: '',
        coverFile: null,
        coverPreview: '',
        coverImagePath: '',
        coverImageUrl: '',
        coverPrompt: '',
        coverLoading: false,
        coverError: '',

        init() {
            if (this.step === 3) {
                this.$nextTick(() => this.initEmailEditor());
            }
            this.$watch('step', (val) => {
                if (val === 3) {
                    this.$nextTick(() => this.initEmailEditor());
                }
            });
        },
        initEmailEditor() {
            if (tinymce.get('email_body_html')) return;
            tinymce.init({
                selector: '#email_body_html',
                height: 300,
                menubar: false,
                plugins: 'lists link code',
                toolbar: 'undo redo | bold italic | bullist numlist | link | removeformat | code',
                skin: 'oxide-dark',
                content_css: 'dark',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; color: #e5e7eb; background: #374151; }',
                branding: false,
                promotion: false
            });
        },
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
            formData.append('context', this.context);
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
                    if (d.target_audience && Array.isArray(d.target_audience)) {
                        this.targetAudience = d.target_audience;
                    }
                    if (d.faq && Array.isArray(d.faq)) {
                        this.faq = d.faq;
                    }
                    if (d.cover_prompt) {
                        this.coverPrompt = d.cover_prompt;
                    }
                }

                // Go to step 2 (cover) instead of step 3
                this.step = 2;

            } catch (e) {
                this.error = 'Network error. Please try again.';
            }

            this.loading = false;
        },

        async generateCover() {
            if (!this.coverPrompt.trim()) return;
            this.coverLoading = true;
            this.coverError = '';

            const formData = new FormData();
            formData.append('prompt', this.coverPrompt);
            formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= generateCsrfToken() ?>');

            try {
                const response = await fetch('/admin/lead-magnets/ai-cover', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (!result.success) {
                    this.coverError = result.error || 'Failed to generate cover. Please try again.';
                    this.coverLoading = false;
                    return;
                }

                this.coverImagePath = result.cover_image_path;
                this.coverImageUrl = result.cover_image_url;
                this.coverPreview = result.cover_image_url;
                this.coverFile = null; // Clear any manual upload

            } catch (e) {
                this.coverError = 'Network error. Please try again.';
            }

            this.coverLoading = false;
        },

        async uploadCover(file) {
            if (!file) return;
            this.coverLoading = true;
            this.coverError = '';
            this.coverPreview = URL.createObjectURL(file);

            const formData = new FormData();
            formData.append('cover_image', file);
            formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= generateCsrfToken() ?>');

            try {
                const response = await fetch('/admin/lead-magnets/upload-cover', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (!result.success) {
                    this.coverError = result.error || 'Failed to upload cover.';
                    this.coverPreview = '';
                    this.coverLoading = false;
                    return;
                }

                this.coverImagePath = result.cover_image_path;
                this.coverImageUrl = result.cover_image_url;
                this.coverPreview = result.cover_image_url;

            } catch (e) {
                this.coverError = 'Network error. Please try again.';
                this.coverPreview = '';
            }

            this.coverLoading = false;
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
