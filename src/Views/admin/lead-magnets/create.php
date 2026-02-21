<?php
$pageTitle = 'Create Lead Magnet';
$currentPage = 'lead-magnets';
$tenant = currentTenant();
$aiConfigured = \App\Services\OpenAIService::isConfigured();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/lead-magnets" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Lead Magnets
    </a>
</div>

<div x-data="leadMagnetWizard()" x-cloak>

    <!-- Step indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-2 sm:space-x-4 flex-wrap gap-y-2">
            <template x-for="(s, i) in stepLabels" :key="i">
                <div class="flex items-center">
                    <div class="flex items-center space-x-1.5">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition"
                             :class="step >= s.n ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                            <template x-if="step > s.n">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="step <= s.n">
                                <span x-text="s.n"></span>
                            </template>
                        </div>
                        <span class="text-xs hidden sm:inline" :class="step >= s.n ? 'text-white' : 'text-gray-500'" x-text="s.label"></span>
                    </div>
                    <template x-if="i < stepLabels.length - 1">
                        <div class="w-6 sm:w-10 h-px mx-1 sm:mx-2" :class="step > s.n ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Step 1: Upload PDF + AI Generate -->
    <div x-show="step === 1" x-transition>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 bg-indigo-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Create with AI</h2>
                <p class="text-gray-500 mb-8">Upload your PDF and let AI generate the landing page content, email copy, and more.</p>

                <div x-show="!loading">
                    <label class="block mb-4">
                        <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 hover:border-indigo-500 transition cursor-pointer"
                             :class="pdfFile ? 'border-indigo-500 bg-indigo-500/5' : ''">
                            <input type="file" accept=".pdf" @change="pdfFile = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div x-show="!pdfFile" class="text-center">
                                <svg class="w-10 h-10 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-gray-500">Click to select a PDF file</p>
                            </div>
                            <div x-show="pdfFile" class="flex items-center justify-center space-x-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="text-white font-medium" x-text="pdfFile?.name"></span>
                            </div>
                        </div>
                    </label>

                    <!-- Additional context textarea -->
                    <div class="mb-6 text-left">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional context <span class="text-gray-500">(optional)</span></label>
                        <textarea x-model="context" rows="3"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                            placeholder="Describe your target audience, goals, or anything that helps AI generate better content..."></textarea>
                    </div>

                    <template x-if="error">
                        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-600 text-sm" x-text="error"></div>
                    </template>

                    <div class="flex items-center justify-center space-x-4">
                        <button @click="generateWithAI()" :disabled="!pdfFile"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition inline-flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Generate with AI</span>
                        </button>
                        <button @click="step = 3" class="text-sm text-gray-500 hover:text-gray-900 transition">
                            Skip AI &rarr;
                        </button>
                    </div>
                </div>

                <!-- Loading state with progress phases -->
                <div x-show="loading" class="py-8">
                    <div class="flex items-center justify-center space-x-3 mb-4">
                        <svg class="animate-spin w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-white font-medium" x-text="loadingPhase"></span>
                    </div>
                    <p class="text-gray-500 text-sm">This may take 30-45 seconds</p>
                    <!-- Progress bar -->
                    <div class="mt-4 max-w-xs mx-auto bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-500 h-full rounded-full transition-all duration-1000" :style="'width: ' + loadingProgress + '%'"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Book Cover -->
    <div x-show="step === 2" x-transition>
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Book Cover</h2>
                <p class="text-gray-500">Add a cover image for the 3D book mockup on your landing page.</p>
            </div>

            <!-- Partial success warning -->
            <template x-if="partialWarning">
                <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl flex items-start space-x-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div>
                        <p class="text-yellow-300 font-medium">Some AI sections could not be generated</p>
                        <p class="text-gray-500 text-sm">You can fill in the missing sections manually in later steps.</p>
                    </div>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Option 1: Upload a cover -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 cursor-pointer hover:border-indigo-500/50 transition"
                     :class="coverMode === 'upload' ? 'border-indigo-500 ring-1 ring-indigo-500/30' : ''"
                     @click="coverMode = 'upload'">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Upload a cover</h3>
                            <p class="text-gray-500 text-sm">Use your own cover image</p>
                        </div>
                    </div>
                    <div x-show="coverMode === 'upload'" x-transition>
                        <label class="block">
                            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-500 transition cursor-pointer text-center">
                                <input type="file" accept="image/*" @click.stop
                                    @change="uploadCover($event.target.files[0])"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <template x-if="!coverPreview && !coverLoading">
                                    <p class="text-gray-500 text-sm">Click to select an image</p>
                                </template>
                                <template x-if="coverLoading">
                                    <div class="flex items-center justify-center space-x-2 py-2">
                                        <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
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
                            <div class="mt-2 p-2 bg-red-500/10 border border-red-500/20 rounded-lg text-red-600 text-xs" x-text="coverError"></div>
                        </template>
                    </div>
                </div>

                <!-- Option 2: Generate with AI -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 cursor-pointer hover:border-indigo-500/50 transition"
                     :class="coverMode === 'ai' ? 'border-indigo-500 ring-1 ring-indigo-500/30' : ''"
                     @click="coverMode = 'ai'">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-indigo-500/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Generate with AI</h3>
                            <p class="text-gray-500 text-sm">DALL-E creates a unique cover</p>
                        </div>
                    </div>
                    <div x-show="coverMode === 'ai'" x-transition @click.stop>
                        <textarea x-model="coverPrompt" rows="3"
                            class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-3"
                            placeholder="Describe the visual style for your cover..."></textarea>

                        <div x-show="!coverLoading && !coverPreview">
                            <button type="button" @click="generateCover()"
                                :disabled="!coverPrompt.trim()"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-medium rounded-lg transition text-sm inline-flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Generate Cover</span>
                            </button>
                        </div>

                        <div x-show="coverLoading" class="text-center py-4">
                            <div class="flex items-center justify-center space-x-2 mb-2">
                                <svg class="animate-spin w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-indigo-300 text-sm font-medium">Generating your book cover...</span>
                            </div>
                            <p class="text-gray-500 text-xs">This takes about 15-20 seconds</p>
                        </div>

                        <div x-show="coverPreview && !coverLoading">
                            <img :src="coverPreview" class="max-h-48 mx-auto rounded-lg mb-3" alt="AI cover preview">
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="coverPreview = ''; coverImagePath = ''; coverImageUrl = '';"
                                    class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                                    Retry
                                </button>
                                <span class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm text-center">
                                    Accepted
                                </span>
                            </div>
                        </div>

                        <template x-if="coverError">
                            <div class="mt-3 p-2 bg-red-500/10 border border-red-500/20 rounded-lg text-red-600 text-xs" x-text="coverError"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-8">
                <button type="button" @click="step = 1" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                    &larr; Back
                </button>
                <div class="flex items-center space-x-4">
                    <button type="button" @click="step = 3" class="text-sm text-gray-500 hover:text-gray-900 transition">
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

    <!-- Steps 3-6 are inside the form -->
    <form method="POST" action="/admin/lead-magnets/gem" enctype="multipart/form-data"
          x-show="step >= 3" x-transition>
        <?= csrfField() ?>

        <!-- Hidden fields for pre-uploaded data -->
        <input type="hidden" name="pdf_filename_existing" :value="pdfFilename">
        <input type="hidden" name="pdf_original_name_existing" :value="pdfOriginalName">
        <input type="hidden" name="cover_image_path_existing" :value="coverImagePath">

        <!-- Hidden JSON fields -->
        <input type="hidden" name="features" :value="JSON.stringify(features)">
        <input type="hidden" name="chapters" :value="JSON.stringify(chapters)">
        <input type="hidden" name="key_statistics" :value="JSON.stringify(keyStatistics)">
        <input type="hidden" name="target_audience" :value="JSON.stringify(targetAudience)">
        <input type="hidden" name="faq" :value="JSON.stringify(faq)">
        <input type="hidden" name="before_after" :value="JSON.stringify(beforeAfter)">
        <input type="hidden" name="testimonial_templates" :value="JSON.stringify(testimonialTemplates)">
        <input type="hidden" name="social_proof" :value="JSON.stringify(socialProof)">

        <!-- Step 3: Basic Info & Hero -->
        <div x-show="step === 3" x-transition class="space-y-8">
            <template x-if="aiGenerated">
                <div class="p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-start space-x-3">
                    <svg class="w-5 h-5 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <div>
                        <p class="text-indigo-300 font-medium">AI-generated content</p>
                        <p class="text-gray-500 text-sm">Review and edit the fields below before saving.</p>
                    </div>
                </div>
            </template>

            <!-- Basic Information -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" name="title" id="title" required x-model="formData.title"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., 10 Tips for Better Marketing">
                    </div>
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                        <input type="text" name="slug" id="slug" required x-model="formData.slug"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="10-tips-for-better-marketing">
                    </div>
                    <div class="md:col-span-2">
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input type="text" name="subtitle" id="subtitle" x-model="formData.subtitle"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="A short subtitle for the lead magnet">
                    </div>
                    <div class="md:col-span-2">
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <input type="text" name="meta_description" id="meta_description" maxlength="160" x-model="formData.meta_description"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="SEO description (max 160 characters)">
                    </div>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Hero Section</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="hero_headline" class="block text-sm font-medium text-gray-700 mb-2">Hero Headline</label>
                        <input type="text" name="hero_headline" id="hero_headline" x-model="formData.hero_headline"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Main headline on the landing page">
                    </div>
                    <div class="md:col-span-2">
                        <label for="hero_subheadline" class="block text-sm font-medium text-gray-700 mb-2">Hero Subheadline</label>
                        <input type="text" name="hero_subheadline" id="hero_subheadline" x-model="formData.hero_subheadline"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Supporting text below the headline">
                    </div>
                    <div>
                        <label for="hero_cta_text" class="block text-sm font-medium text-gray-700 mb-2">Hero CTA Text</label>
                        <input type="text" name="hero_cta_text" id="hero_cta_text" x-model="formData.hero_cta_text"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Download Free Guide">
                    </div>
                    <div>
                        <label for="hero_bg_color" class="block text-sm font-medium text-gray-700 mb-2">Hero Background Color</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" name="hero_bg_color" id="hero_bg_color" x-model="formData.hero_bg_color"
                                class="w-12 h-10 bg-white border border-gray-300 rounded-lg cursor-pointer">
                            <input type="text" x-model="formData.hero_bg_color"
                                class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="#1e1b4b">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="hero_image" class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                        <input type="file" name="hero_image" id="hero_image" accept="image/*"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Cover Image Preview (if set in step 2) -->
            <template x-if="coverImagePath">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Book Cover</h3>
                    <div class="flex items-start space-x-4">
                        <img :src="coverImageUrl" class="h-32 rounded-lg border border-gray-300" alt="Cover preview">
                        <div>
                            <p class="text-gray-500 text-sm">This cover will be displayed as a 3D book mockup on the landing page.</p>
                            <button type="button" @click="step = 2" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                Change cover
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Navigation -->
            <div class="flex items-center justify-between">
                <button type="button" @click="step = 2" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                    &larr; Back to Cover
                </button>
                <button type="button" @click="step = 4" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                    Next: Content Sections &rarr;
                </button>
            </div>
        </div>

        <!-- Step 4: Content Sections -->
        <div x-show="step === 4" x-transition class="space-y-8">

            <!-- Features -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Features Section</h3>
                <div class="space-y-6">
                    <div>
                        <label for="features_headline" class="block text-sm font-medium text-gray-700 mb-2">Features Headline</label>
                        <input type="text" name="features_headline" id="features_headline" x-model="formData.features_headline"
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
                    </div>
                </div>
            </div>

            <!-- Chapters / Table of Contents -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Chapters / Table of Contents</h3>
                <p class="text-gray-500 text-sm mb-6">Displayed as a numbered list on the landing page showing what the PDF covers.</p>
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
                <p class="text-gray-500 text-sm mb-6">Bold stat cards displayed on the landing page. Use real numbers from your PDF where possible.</p>
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

            <!-- Navigation -->
            <div class="flex items-center justify-between">
                <button type="button" @click="step = 3" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                    &larr; Back
                </button>
                <button type="button" @click="step = 5" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                    Next: Trust & Audience &rarr;
                </button>
            </div>
        </div>

        <!-- Step 5: Trust & Audience -->
        <div x-show="step === 5" x-transition class="space-y-8">

            <!-- Target Audience -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Target Audience</h3>
                <p class="text-gray-500 text-sm mb-6">Define who this lead magnet is for. These appear as persona cards on the landing page.</p>
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
                <p class="text-gray-500 text-sm mb-6">Show the contrast between the reader's current situation and the outcome after reading your PDF.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Before -->
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
                    <!-- After -->
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Testimonial Templates</h3>
                <p class="text-gray-500 text-sm mb-6">Edit these to match real feedback. AI-generated as starting points.</p>
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

            <!-- Social Proof -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Social Proof Bar</h3>
                <p class="text-gray-500 text-sm mb-6">Customizable metrics bar shown below the hero. Replaces the default "PDF Guide / 100% Free / Instant Access".</p>
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

            <!-- FAQ -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">FAQ</h3>
                <p class="text-gray-500 text-sm mb-6">Common questions prospects might have before downloading. Displayed as an accordion on the landing page.</p>
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

            <!-- Navigation -->
            <div class="flex items-center justify-between">
                <button type="button" @click="step = 4" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                    &larr; Back
                </button>
                <button type="button" @click="goToStep6()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                    Next: Email & Publish &rarr;
                </button>
            </div>
        </div>

        <!-- Step 6: Email & Publishing -->
        <div x-show="step === 6" x-transition class="space-y-8">

            <!-- PDF File -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Downloadable PDF</h3>
                <div>
                    <template x-if="pdfFilename">
                        <div class="flex items-center space-x-3 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-green-300 text-sm">PDF uploaded: <span x-text="pdfOriginalName" class="font-medium"></span></span>
                        </div>
                    </template>
                    <template x-if="!pdfFilename">
                        <div>
                            <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">PDF File</label>
                            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                            <p class="text-xs text-gray-500 mt-2">Upload the PDF file that will be delivered after signup.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Email Delivery</h3>
                <div class="space-y-6">
                    <div>
                        <label for="email_subject" class="block text-sm font-medium text-gray-700 mb-2">Email Subject</label>
                        <input type="text" name="email_subject" id="email_subject" x-model="formData.email_subject"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Here's your free guide!">
                    </div>
                    <div>
                        <label for="email_body_html" class="block text-sm font-medium text-gray-700 mb-2">Email Body</label>
                        <textarea name="email_body_html" id="email_body_html" rows="8" x-model="formData.email_body_html"
                            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="The email content that will be sent with the download link..."></textarea>
                        <p class="text-xs text-gray-500 mt-2">Use {{download_link}} for the PDF link. Use {{name}} for the recipient's name.</p>
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
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between">
                <button type="button" @click="step = 5" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                    &larr; Back
                </button>
                <div class="flex items-center space-x-4">
                    <a href="/admin/lead-magnets" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        Create Lead Magnet
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function leadMagnetWizard() {
    return {
        step: <?= $aiConfigured ? '1' : '3' ?>,
        stepLabels: [
            {n: 1, label: 'Upload PDF'},
            {n: 2, label: 'Book Cover'},
            {n: 3, label: 'Basic Info'},
            {n: 4, label: 'Content'},
            {n: 5, label: 'Trust'},
            {n: 6, label: 'Publish'}
        ],
        loading: false,
        loadingPhase: 'Analyzing your PDF...',
        loadingProgress: 0,
        error: '',
        context: '',
        pdfFile: null,
        pdfFilename: '',
        pdfOriginalName: '',
        aiGenerated: false,
        partialWarning: false,
        features: [],
        chapters: [],
        keyStatistics: [],
        targetAudience: [],
        faq: [],
        beforeAfter: { before: [], after: [] },
        authorBio: '',
        testimonialTemplates: [],
        socialProof: [],

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
            this.$watch('step', (val) => {
                if (val === 6) {
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
                skin: 'oxide',
                content_css: 'default',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; color: #1f2937; background: #ffffff; }',
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

        goToStep6() {
            this.step = 6;
        },

        startLoadingAnimation() {
            this.loadingProgress = 0;
            this.loadingPhase = 'Analyzing your PDF...';

            setTimeout(() => { this.loadingProgress = 20; }, 500);
            setTimeout(() => {
                this.loadingPhase = 'Analyzing content structure...';
                this.loadingProgress = 35;
            }, 5000);
            setTimeout(() => {
                this.loadingPhase = 'Generating landing page sections...';
                this.loadingProgress = 55;
            }, 12000);
            setTimeout(() => {
                this.loadingPhase = 'Building trust elements...';
                this.loadingProgress = 70;
            }, 20000);
            setTimeout(() => {
                this.loadingPhase = 'Almost done...';
                this.loadingProgress = 85;
            }, 30000);
        },

        async generateWithAI() {
            if (!this.pdfFile) return;
            this.loading = true;
            this.error = '';
            this.startLoadingAnimation();

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
                    this.partialWarning = result.partial || false;
                    const d = result.data;

                    // Basic info & hero
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

                    // Content sections
                    if (d.features && Array.isArray(d.features)) this.features = d.features;
                    if (d.chapters && Array.isArray(d.chapters)) this.chapters = d.chapters;
                    if (d.key_statistics && Array.isArray(d.key_statistics)) this.keyStatistics = d.key_statistics;

                    // Trust sections
                    if (d.target_audience && Array.isArray(d.target_audience)) this.targetAudience = d.target_audience;
                    if (d.faq && Array.isArray(d.faq)) this.faq = d.faq;
                    if (d.before_after && typeof d.before_after === 'object') {
                        this.beforeAfter = {
                            before: d.before_after.before || [],
                            after: d.before_after.after || []
                        };
                    }
                    if (d.author_bio) this.authorBio = d.author_bio;
                    if (d.testimonial_templates && Array.isArray(d.testimonial_templates)) this.testimonialTemplates = d.testimonial_templates;
                    if (d.social_proof && Array.isArray(d.social_proof)) this.socialProof = d.social_proof;

                    if (d.cover_prompt) this.coverPrompt = d.cover_prompt;
                }

                this.loadingProgress = 100;
                setTimeout(() => { this.step = 2; }, 300);

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
                this.coverFile = null;

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
