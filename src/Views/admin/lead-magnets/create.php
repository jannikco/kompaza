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

    <!-- ==================== STEP 1: Upload & Generate ==================== -->
    <template x-if="step === 1">
        <div>
            <div class="max-w-2xl mx-auto">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-8 text-center">
                    <div class="w-16 h-16 bg-indigo-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Create with AI</h2>
                    <p class="text-gray-500 mb-8">Upload your PDF and let AI generate the landing page content, email copy, and more.</p>

                    <!-- Phase 1: Upload & Analyze -->
                    <div x-show="!analyzing && !showLanguageSelection && !generating">
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
                                    <span class="text-gray-900 font-medium" x-text="pdfFile?.name"></span>
                                </div>
                            </div>
                        </label>

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
                            <button @click="analyzeWithAI()" :disabled="!pdfFile"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition inline-flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Generate with AI</span>
                            </button>
                            <button @click="step = 3" class="text-sm text-gray-500 hover:text-gray-900 transition">
                                Skip AI &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Analyzing spinner -->
                    <div x-show="analyzing" class="py-8">
                        <div class="flex items-center justify-center space-x-3 mb-4">
                            <svg class="animate-spin w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-gray-900 font-medium">Analyzing your PDF...</span>
                        </div>
                        <p class="text-gray-500 text-sm">This usually takes 5-10 seconds</p>
                    </div>

                    <!-- Language Selection -->
                    <div x-show="showLanguageSelection" class="py-4">
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start space-x-3">
                            <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <div class="text-left">
                                <p class="text-green-800 font-medium">PDF analyzed successfully</p>
                                <p class="text-green-700 text-sm">Language detected: <strong x-text="languageNames[detectedLanguage] || detectedLanguage"></strong></p>
                            </div>
                        </div>

                        <div class="mb-6 text-left">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Generate content in:</label>
                            <select x-model="confirmedLanguage"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="da">Danish</option>
                                <option value="en">English</option>
                                <option value="de">German</option>
                                <option value="fr">French</option>
                                <option value="es">Spanish</option>
                                <option value="nl">Dutch</option>
                                <option value="sv">Swedish</option>
                                <option value="no">Norwegian</option>
                            </select>
                        </div>

                        <template x-if="error">
                            <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-600 text-sm" x-text="error"></div>
                        </template>

                        <div class="flex items-center justify-center space-x-4">
                            <button @click="generateContent()"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition inline-flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Generate Landing Page</span>
                            </button>
                            <button @click="showLanguageSelection = false; analyzing = false; error = '';" class="text-sm text-gray-500 hover:text-gray-900 transition">
                                &larr; Back
                            </button>
                        </div>
                    </div>

                    <!-- Generating spinner -->
                    <div x-show="generating" class="py-8">
                        <div class="flex items-center justify-center space-x-3 mb-4">
                            <svg class="animate-spin w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-gray-900 font-medium" x-text="loadingPhase"></span>
                        </div>
                        <p class="text-gray-500 text-sm">This may take 30-45 seconds</p>
                        <div class="mt-4 max-w-xs mx-auto bg-gray-200 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-indigo-500 h-full rounded-full transition-all duration-1000" :style="'width: ' + loadingProgress + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- ==================== STEP 2: Pick Your Style ==================== -->
    <template x-if="step === 2">
        <div>
            <div class="max-w-6xl mx-auto">

                <!-- Partial success warning -->
                <template x-if="partialWarning">
                    <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl flex items-start space-x-3">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div>
                            <p class="text-yellow-700 font-medium">Some AI sections could not be generated</p>
                            <p class="text-gray-500 text-sm">You can fill in the missing sections manually in later steps.</p>
                        </div>
                    </div>
                </template>

                <!-- Hero Variants -->
                <div class="mb-10">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Pick Your Hero Style</h2>
                        <p class="text-gray-500">AI generated 3 headline variants. Click to select your favorite.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <template x-for="(variant, idx) in heroVariants" :key="idx">
                            <div @click="selectHeroVariant(idx)"
                                 class="bg-white border-2 rounded-xl p-6 cursor-pointer transition hover:shadow-md"
                                 :class="selectedVariantIndex === idx ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                                <!-- Badge -->
                                <div class="mb-3">
                                    <span class="inline-block bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-semibold" x-text="variant.hero_badge || 'Free Guide'"></span>
                                </div>
                                <!-- Headline with accent preview -->
                                <h3 class="text-lg font-bold text-gray-900 mb-2 leading-snug" x-html="highlightAccent(variant.hero_headline, variant.hero_headline_accent)"></h3>
                                <!-- Subheadline -->
                                <p class="text-gray-500 text-sm mb-4" x-text="variant.hero_subheadline"></p>
                                <!-- CTA preview -->
                                <div class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium" x-text="variant.hero_cta_text"></div>
                                <!-- Selection indicator -->
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-xs text-gray-400" x-text="['Bold & Direct', 'Curiosity-Driven', 'Benefit-Focused'][idx]"></span>
                                    <div x-show="selectedVariantIndex === idx" class="flex items-center space-x-1 text-indigo-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-xs font-medium">Selected</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Fallback if no variants -->
                    <template x-if="heroVariants.length === 0">
                        <div class="text-center text-gray-500 py-4 text-sm">No hero variants were generated. You can edit the hero text manually in the next step.</div>
                    </template>
                </div>

                <!-- Book Covers -->
                <div class="mb-10">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Choose a Book Cover</h2>
                        <p class="text-gray-500">AI-generated covers for the 3D book mockup on your landing page.</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- AI Cover variants -->
                        <template x-for="(cover, idx) in coverVariants" :key="'cover-'+idx">
                            <div @click="selectCover(idx)"
                                 class="border-2 rounded-xl overflow-hidden cursor-pointer transition hover:shadow-md"
                                 :class="selectedCoverIndex === idx ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200'">
                                <div class="aspect-[2/3] bg-gray-100 relative">
                                    <img :src="cover.cover_image_url" class="w-full h-full object-cover" alt="Cover option">
                                    <div x-show="selectedCoverIndex === idx"
                                         class="absolute top-2 right-2 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Loading placeholders -->
                        <template x-if="coversLoading && coverVariants.length === 0">
                            <template x-for="i in 3" :key="'loading-'+i">
                                <div class="border-2 border-gray-200 rounded-xl overflow-hidden">
                                    <div class="aspect-[2/3] bg-gray-100 flex items-center justify-center">
                                        <div class="text-center">
                                            <svg class="animate-spin w-8 h-8 text-indigo-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="text-gray-400 text-xs">Generating...</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>

                        <!-- Upload your own -->
                        <div class="border-2 border-dashed border-gray-300 rounded-xl overflow-hidden cursor-pointer hover:border-indigo-500 transition relative"
                             :class="selectedCoverIndex === -2 ? 'border-indigo-500 ring-2 ring-indigo-500/20' : ''">
                            <label class="block cursor-pointer">
                                <input type="file" accept="image/*" @change="uploadOwnCover($event.target.files[0])" class="hidden">
                                <div class="aspect-[2/3] flex items-center justify-center">
                                    <template x-if="!uploadedCoverPreview">
                                        <div class="text-center p-4">
                                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="text-gray-500 text-xs font-medium">Upload your own</p>
                                        </div>
                                    </template>
                                    <template x-if="uploadedCoverPreview">
                                        <div class="w-full h-full relative">
                                            <img :src="uploadedCoverPreview" class="w-full h-full object-cover" alt="Uploaded cover">
                                            <div x-show="selectedCoverIndex === -2"
                                                 class="absolute top-2 right-2 w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </label>
                        </div>
                    </div>

                    <template x-if="coverError">
                        <div class="mt-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-600 text-sm" x-text="coverError"></div>
                    </template>
                </div>

                <!-- Hero background color -->
                <div class="mb-8 max-w-md mx-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hero Background Color</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" x-model="formData.hero_bg_color" class="w-12 h-10 bg-white border border-gray-300 rounded-lg cursor-pointer">
                        <input type="text" x-model="formData.hero_bg_color"
                            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="#1e1b4b">
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between">
                    <button type="button" @click="step = 1" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                        &larr; Back
                    </button>
                    <div class="flex items-center space-x-4">
                        <button type="button" @click="step = 3" class="text-sm text-gray-500 hover:text-gray-900 transition">
                            Skip &rarr;
                        </button>
                        <button type="button" @click="step = 3"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                            Next: Review Content &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Steps 3-4 are inside the form -->
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
        <input type="hidden" name="language" :value="confirmedLanguage">
        <input type="hidden" name="section_headings" :value="JSON.stringify(sectionHeadings)">
        <input type="hidden" name="hero_badge" :value="formData.hero_badge">
        <input type="hidden" name="hero_headline_accent" :value="formData.hero_headline_accent">

        <!-- ==================== STEP 3: Review Content (Accordion) ==================== -->
        <template x-if="step === 3">
            <div class="space-y-4 max-w-4xl mx-auto">
                <template x-if="aiGenerated">
                    <div class="p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-start space-x-3 mb-4">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <div>
                            <p class="text-indigo-700 font-medium">AI-generated content</p>
                            <p class="text-gray-500 text-sm">Expand any section to review and edit. Click the section headers below.</p>
                        </div>
                    </div>
                </template>

                <!-- 1. Basic Info -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'basic' ? '' : 'basic'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="formData.title ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="formData.title" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!formData.title" class="text-xs">1</span>
                            </div>
                            <span class="font-semibold text-gray-900">Basic Info</span>
                            <span class="text-xs text-gray-400" x-show="formData.title" x-text="formData.title"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'basic' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'basic'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <input type="text" name="title" required x-model="formData.title"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="e.g., 10 Tips for Better Marketing">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                    <input type="text" name="slug" required x-model="formData.slug"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="10-tips-for-better-marketing">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" name="subtitle" x-model="formData.subtitle"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="A short subtitle">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                    <input type="text" name="meta_description" maxlength="160" x-model="formData.meta_description"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="SEO description (max 160 characters)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Hero Section -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'hero' ? '' : 'hero'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="formData.hero_headline ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="formData.hero_headline" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!formData.hero_headline" class="text-xs">2</span>
                            </div>
                            <span class="font-semibold text-gray-900">Hero Section</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'hero' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'hero'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hero Headline</label>
                                <input type="text" name="hero_headline" x-model="formData.hero_headline"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Main headline">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hero Subheadline</label>
                                <input type="text" name="hero_subheadline" x-model="formData.hero_subheadline"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Supporting text">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA Text</label>
                                    <input type="text" name="hero_cta_text" x-model="formData.hero_cta_text"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Download Free Guide">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Badge Text</label>
                                    <input type="text" x-model="formData.hero_badge"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Free Guide">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Headline Accent Words</label>
                                    <input type="text" x-model="formData.hero_headline_accent"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Key words to highlight in brand color">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="color" name="hero_bg_color" x-model="formData.hero_bg_color"
                                            class="w-12 h-10 bg-white border border-gray-300 rounded-lg cursor-pointer">
                                        <input type="text" x-model="formData.hero_bg_color"
                                            class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hero Image</label>
                                <input type="file" name="hero_image" accept="image/*"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Book Cover Preview -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'cover' ? '' : 'cover'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="coverImagePath ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="coverImagePath" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!coverImagePath" class="text-xs">3</span>
                            </div>
                            <span class="font-semibold text-gray-900">Book Cover</span>
                            <span class="text-xs text-gray-400" x-show="coverImagePath">Cover selected</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'cover' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'cover'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4">
                            <template x-if="coverImagePath">
                                <div class="flex items-start space-x-4">
                                    <img :src="coverImageUrl" class="h-32 rounded-lg border border-gray-300" alt="Cover preview">
                                    <div>
                                        <p class="text-gray-500 text-sm">This cover will be displayed as a 3D book mockup on the landing page.</p>
                                        <button type="button" @click="step = 2" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500 transition">Change cover</button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!coverImagePath">
                                <div class="text-center py-4">
                                    <p class="text-gray-500 text-sm mb-2">No cover selected.</p>
                                    <button type="button" @click="step = 2" class="text-sm text-indigo-600 hover:text-indigo-500 transition">Go back to pick a cover</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 4. Features -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'features' ? '' : 'features'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="features.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="features.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!features.length" class="text-xs">4</span>
                            </div>
                            <span class="font-semibold text-gray-900">Features</span>
                            <span class="text-xs text-gray-400" x-show="features.length" x-text="features.length + ' items'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'features' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'features'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Features Headline</label>
                                <input type="text" name="features_headline" x-model="formData.features_headline"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="e.g., What You'll Learn">
                            </div>
                            <div class="space-y-3">
                                <template x-for="(feature, index) in features" :key="index">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-1 space-y-2">
                                                <input type="text" x-model="feature.title"
                                                    class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                    placeholder="Feature title">
                                                <input type="text" x-model="feature.description"
                                                    class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                    placeholder="Feature description">
                                            </div>
                                            <button type="button" @click="features.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="features.push({title: '', description: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add feature</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 5. Chapters -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'chapters' ? '' : 'chapters'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="chapters.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="chapters.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!chapters.length" class="text-xs">5</span>
                            </div>
                            <span class="font-semibold text-gray-900">Chapters</span>
                            <span class="text-xs text-gray-400" x-show="chapters.length" x-text="chapters.length + ' chapters'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'chapters' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'chapters'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-3">
                            <template x-for="(chapter, index) in chapters" :key="index">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 bg-indigo-500/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                            <span class="text-indigo-600 font-bold text-xs" x-text="chapter.number || (index + 1)"></span>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <input type="text" x-model="chapter.title"
                                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                placeholder="Chapter title">
                                            <input type="text" x-model="chapter.description"
                                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                placeholder="Brief description">
                                        </div>
                                        <button type="button" @click="chapters.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="chapters.push({number: chapters.length + 1, title: '', description: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add chapter</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 6. Statistics -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'stats' ? '' : 'stats'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="keyStatistics.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="keyStatistics.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!keyStatistics.length" class="text-xs">6</span>
                            </div>
                            <span class="font-semibold text-gray-900">Statistics</span>
                            <span class="text-xs text-gray-400" x-show="keyStatistics.length" x-text="keyStatistics.length + ' stats'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'stats' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'stats'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-3">
                            <template x-for="(stat, index) in keyStatistics" :key="index">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" x-model="stat.icon" maxlength="4"
                                            class="w-14 px-2 py-2 bg-white border border-gray-300 text-gray-900 text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Icon">
                                        <input type="text" x-model="stat.value"
                                            class="w-24 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="e.g., 50+">
                                        <input type="text" x-model="stat.label"
                                            class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Label">
                                        <button type="button" @click="keyStatistics.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="keyStatistics.push({value: '', label: '', icon: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add statistic</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 7. Transformation -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'transform' ? '' : 'transform'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="beforeAfter.before.length || beforeAfter.after.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="beforeAfter.before.length || beforeAfter.after.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!beforeAfter.before.length && !beforeAfter.after.length" class="text-xs">7</span>
                            </div>
                            <span class="font-semibold text-gray-900">Transformation</span>
                            <span class="text-xs text-gray-400" x-show="beforeAfter.before.length" x-text="beforeAfter.before.length + ' before / ' + beforeAfter.after.length + ' after'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'transform' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'transform'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-red-600 mb-2">Before (Pain Points)</label>
                                    <div class="space-y-2">
                                        <template x-for="(item, index) in beforeAfter.before" :key="'before-'+index">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-red-600 flex-shrink-0">&#x2717;</span>
                                                <input type="text" x-model="beforeAfter.before[index]"
                                                    class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Pain point...">
                                                <button type="button" @click="beforeAfter.before.splice(index, 1)" class="p-1 text-gray-400 hover:text-red-600 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="beforeAfter.before.push('')" class="mt-2 text-xs text-red-600 hover:text-red-500 transition">+ Add pain point</button>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-green-600 mb-2">After (Outcomes)</label>
                                    <div class="space-y-2">
                                        <template x-for="(item, index) in beforeAfter.after" :key="'after-'+index">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-green-600 flex-shrink-0">&#x2713;</span>
                                                <input type="text" x-model="beforeAfter.after[index]"
                                                    class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Positive outcome...">
                                                <button type="button" @click="beforeAfter.after.splice(index, 1)" class="p-1 text-gray-400 hover:text-green-600 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" @click="beforeAfter.after.push('')" class="mt-2 text-xs text-green-600 hover:text-green-500 transition">+ Add outcome</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. Target Audience -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'audience' ? '' : 'audience'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="targetAudience.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="targetAudience.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!targetAudience.length" class="text-xs">8</span>
                            </div>
                            <span class="font-semibold text-gray-900">Target Audience</span>
                            <span class="text-xs text-gray-400" x-show="targetAudience.length" x-text="targetAudience.length + ' personas'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'audience' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'audience'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-3">
                            <template x-for="(persona, index) in targetAudience" :key="index">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1 space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <input type="text" x-model="persona.icon" maxlength="4"
                                                    class="w-14 px-2 py-2 bg-white border border-gray-300 text-gray-900 text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="&#x1F4BC;">
                                                <input type="text" x-model="persona.title"
                                                    class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Persona title">
                                            </div>
                                            <input type="text" x-model="persona.description"
                                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Why this persona benefits">
                                        </div>
                                        <button type="button" @click="targetAudience.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="targetAudience.push({icon: '', title: '', description: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add persona</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 9. Author Bio -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'author' ? '' : 'author'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="authorBio ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="authorBio" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!authorBio" class="text-xs">9</span>
                            </div>
                            <span class="font-semibold text-gray-900">Author Bio</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'author' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'author'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4">
                            <textarea x-model="authorBio" name="author_bio" rows="3"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                placeholder="Write a short author bio..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- 10. Testimonials -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'testimonials' ? '' : 'testimonials'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="testimonialTemplates.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="testimonialTemplates.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!testimonialTemplates.length" class="text-xs">10</span>
                            </div>
                            <span class="font-semibold text-gray-900">Testimonials</span>
                            <span class="text-xs text-gray-400" x-show="testimonialTemplates.length" x-text="testimonialTemplates.length + ' testimonials'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'testimonials' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'testimonials'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-3">
                            <template x-for="(testimonial, index) in testimonialTemplates" :key="index">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1 space-y-2">
                                            <textarea x-model="testimonial.quote" rows="2"
                                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Testimonial quote..."></textarea>
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" x-model="testimonial.name"
                                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Name">
                                                <input type="text" x-model="testimonial.title"
                                                    class="px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Job title">
                                            </div>
                                        </div>
                                        <button type="button" @click="testimonialTemplates.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="testimonialTemplates.push({quote: '', name: '', title: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add testimonial</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 11. Social Proof -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'social' ? '' : 'social'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="socialProof.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="socialProof.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!socialProof.length" class="text-xs">11</span>
                            </div>
                            <span class="font-semibold text-gray-900">Social Proof</span>
                            <span class="text-xs text-gray-400" x-show="socialProof.length" x-text="socialProof.length + ' metrics'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'social' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'social'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-3">
                            <template x-for="(proof, index) in socialProof" :key="index">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" x-model="proof.icon" maxlength="4"
                                            class="w-14 px-2 py-2 bg-white border border-gray-300 text-gray-900 text-center rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Icon">
                                        <input type="text" x-model="proof.value"
                                            class="w-24 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="e.g., 10,000+">
                                        <input type="text" x-model="proof.label"
                                            class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Label">
                                        <button type="button" @click="socialProof.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="socialProof.push({value: '', label: '', icon: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add metric</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 12. FAQ -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <button type="button" @click="openSection = openSection === 'faq' ? '' : 'faq'"
                        class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center"
                                 :class="faq.length ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'">
                                <svg x-show="faq.length" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!faq.length" class="text-xs">12</span>
                            </div>
                            <span class="font-semibold text-gray-900">FAQ</span>
                            <span class="text-xs text-gray-400" x-show="faq.length" x-text="faq.length + ' questions'"></span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openSection === 'faq' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSection === 'faq'" x-collapse>
                        <div class="px-6 pb-6 border-t border-gray-100 pt-4 space-y-3">
                            <template x-for="(item, index) in faq" :key="index">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1 space-y-2">
                                            <input type="text" x-model="item.question"
                                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Question">
                                            <textarea x-model="item.answer" rows="2"
                                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Answer"></textarea>
                                        </div>
                                        <button type="button" @click="faq.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="faq.push({question: '', answer: ''})"
                                class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add FAQ item</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between pt-4">
                    <button type="button" @click="step = 2" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
                        &larr; Back to Pick Style
                    </button>
                    <button type="button" @click="goToStep4()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition">
                        Next: Email & Publish &rarr;
                    </button>
                </div>
            </div>
        </template>

        <!-- ==================== STEP 4: Email & Publish ==================== -->
        <template x-if="step === 4">
            <div class="space-y-8 max-w-4xl mx-auto">

                <!-- PDF File -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Downloadable PDF</h3>
                    <div>
                        <template x-if="pdfFilename">
                            <div class="flex items-center space-x-3 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-green-700 text-sm">PDF uploaded: <span x-text="pdfOriginalName" class="font-medium"></span></span>
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
                            <input type="hidden" name="email_body_html" id="email_body_html-hidden">
                            <div id="email_body_html-editor" class="bg-white"></div>
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
                    <button type="button" @click="step = 3" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition">
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
        </template>
    </form>
</div>

<script>
function leadMagnetWizard() {
    return {
        step: <?= $aiConfigured ? '1' : '3' ?>,
        stepLabels: [
            {n: 1, label: 'Upload & Generate'},
            {n: 2, label: 'Pick Style'},
            {n: 3, label: 'Review Content'},
            {n: 4, label: 'Email & Publish'}
        ],
        loading: false,
        analyzing: false,
        generating: false,
        showLanguageSelection: false,
        loadingPhase: 'Generating content...',
        loadingProgress: 0,
        error: '',
        context: '',
        pdfFile: null,
        pdfFilename: '',
        pdfOriginalName: '',
        pdfTextForGeneration: '',
        orchestratorData: null,
        detectedLanguage: '',
        confirmedLanguage: '',
        aiGenerated: false,
        partialWarning: false,
        openSection: '',

        // Hero variants
        heroVariants: [],
        selectedVariantIndex: 0,

        // Cover variants
        coverVariants: [],
        selectedCoverIndex: -1,
        coversLoading: false,
        coverError: '',
        coverImagePath: '',
        coverImageUrl: '',
        uploadedCoverPreview: '',
        coverPrompt: '',

        // Content arrays
        features: [],
        chapters: [],
        keyStatistics: [],
        targetAudience: [],
        faq: [],
        beforeAfter: { before: [], after: [] },
        authorBio: '',
        testimonialTemplates: [],
        socialProof: [],
        sectionHeadings: {},

        languageNames: { da: 'Danish', en: 'English', de: 'German', fr: 'French', es: 'Spanish', nl: 'Dutch', sv: 'Swedish', no: 'Norwegian' },

        init() {
            this.$watch('step', (val) => {
                if (val === 4) {
                    this.$nextTick(() => this.initEmailEditor());
                }
            });
        },

        initEmailEditor() {
            if (window._quillEmail) return;
            window._quillEmail = initRichEditor('email_body_html-editor', 'email_body_html-hidden', { simple: true, height: 300 });
            if (this.formData.email_body_html) {
                window._quillEmail.root.innerHTML = this.formData.email_body_html;
                document.getElementById('email_body_html-hidden').value = this.formData.email_body_html;
            }
            const self = this;
            window._quillEmail.on('text-change', function() {
                self.formData.email_body_html = window._quillEmail.root.innerHTML;
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
            hero_badge: '',
            hero_headline_accent: '',
            hero_bg_color: '#1e1b4b',
            features_headline: '',
            email_subject: '',
            email_body_html: '',
        },

        goToStep4() {
            this.step = 4;
        },

        highlightAccent(headline, accent) {
            if (!accent || !headline) return this.escapeHtml(headline || '');
            const escaped = this.escapeHtml(headline);
            const escapedAccent = this.escapeHtml(accent);
            return escaped.replace(escapedAccent, '<span class="text-indigo-600">' + escapedAccent + '</span>');
        },

        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        selectHeroVariant(idx) {
            this.selectedVariantIndex = idx;
            const v = this.heroVariants[idx];
            if (v) {
                this.formData.hero_headline = v.hero_headline || '';
                this.formData.hero_subheadline = v.hero_subheadline || '';
                this.formData.hero_cta_text = v.hero_cta_text || '';
                this.formData.hero_badge = v.hero_badge || '';
                this.formData.hero_headline_accent = v.hero_headline_accent || '';
            }
        },

        selectCover(idx) {
            this.selectedCoverIndex = idx;
            const cover = this.coverVariants[idx];
            if (cover) {
                this.coverImagePath = cover.cover_image_path;
                this.coverImageUrl = cover.cover_image_url;
            }
        },

        startGeneratingAnimation() {
            this.loadingProgress = 0;
            this.loadingPhase = 'Generating landing page sections...';

            setTimeout(() => { this.loadingProgress = 20; }, 500);
            setTimeout(() => {
                this.loadingPhase = 'Creating marketing copy...';
                this.loadingProgress = 40;
            }, 5000);
            setTimeout(() => {
                this.loadingPhase = 'Building trust elements...';
                this.loadingProgress = 60;
            }, 12000);
            setTimeout(() => {
                this.loadingPhase = 'Translating section headings...';
                this.loadingProgress = 75;
            }, 20000);
            setTimeout(() => {
                this.loadingPhase = 'Almost done...';
                this.loadingProgress = 85;
            }, 30000);
        },

        async analyzeWithAI() {
            if (!this.pdfFile) return;
            this.analyzing = true;
            this.error = '';

            const formData = new FormData();
            formData.append('pdf_file', this.pdfFile);
            formData.append('context', this.context);
            formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= generateCsrfToken() ?>');

            try {
                const response = await fetch('/admin/lead-magnets/ai-analyze', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (!result.success) {
                    this.error = result.error || 'Something went wrong. Please try again.';
                    this.analyzing = false;
                    return;
                }

                this.pdfFilename = result.pdf_filename || '';
                this.pdfOriginalName = result.pdf_original_name || '';

                if (!result.ai_generated) {
                    this.analyzing = false;
                    this.step = 3;
                    return;
                }

                this.orchestratorData = result.orchestrator;
                this.pdfTextForGeneration = result.pdf_text || '';
                this.detectedLanguage = result.orchestrator.language || 'en';
                this.confirmedLanguage = this.detectedLanguage;

                this.formData.title = result.orchestrator.title || '';
                this.formData.slug = result.orchestrator.slug || '';
                this.formData.subtitle = result.orchestrator.subtitle || '';
                this.formData.meta_description = result.orchestrator.meta_description || '';
                this.formData.hero_bg_color = result.orchestrator.hero_bg_color || '#1e1b4b';
                if (result.orchestrator.cover_prompt) this.coverPrompt = result.orchestrator.cover_prompt;

                this.analyzing = false;
                this.showLanguageSelection = true;

            } catch (e) {
                this.error = 'Network error. Please try again.';
                this.analyzing = false;
            }
        },

        async generateContent() {
            this.showLanguageSelection = false;
            this.generating = true;
            this.error = '';
            this.startGeneratingAnimation();

            const formData = new FormData();
            formData.append('pdf_text', this.pdfTextForGeneration);
            formData.append('orchestrator', JSON.stringify(this.orchestratorData));
            formData.append('language', this.confirmedLanguage);
            formData.append('context', this.context);
            formData.append('pdf_filename', this.pdfFilename);
            formData.append('pdf_original_name', this.pdfOriginalName);
            formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= generateCsrfToken() ?>');

            try {
                const response = await fetch('/admin/lead-magnets/ai-generate', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (!result.success) {
                    this.error = result.error || 'Something went wrong. Please try again.';
                    this.generating = false;
                    this.showLanguageSelection = true;
                    return;
                }

                if (result.ai_generated && result.data) {
                    this.aiGenerated = true;
                    this.partialWarning = result.partial || false;
                    const d = result.data;

                    // Hero variants
                    if (d.hero_variants && Array.isArray(d.hero_variants) && d.hero_variants.length > 0) {
                        this.heroVariants = d.hero_variants;
                        this.selectHeroVariant(0);
                    } else {
                        // Fallback: use flat fields
                        this.formData.hero_headline = d.hero_headline || '';
                        this.formData.hero_subheadline = d.hero_subheadline || '';
                        this.formData.hero_cta_text = d.hero_cta_text || '';
                        this.formData.hero_badge = d.hero_badge || '';
                        this.formData.hero_headline_accent = d.hero_headline_accent || '';
                    }

                    this.formData.features_headline = d.features_headline || '';
                    this.formData.email_subject = d.email_subject || '';
                    this.formData.email_body_html = d.email_body_html || '';

                    if (d.features && Array.isArray(d.features)) this.features = d.features;
                    if (d.chapters && Array.isArray(d.chapters)) this.chapters = d.chapters;
                    if (d.key_statistics && Array.isArray(d.key_statistics)) this.keyStatistics = d.key_statistics;
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
                    if (d.section_headings && typeof d.section_headings === 'object') this.sectionHeadings = d.section_headings;
                    if (d.language) this.confirmedLanguage = d.language;
                }

                this.loadingProgress = 100;
                setTimeout(() => {
                    this.step = 2;
                    // Auto-trigger cover generation
                    if (this.coverPrompt) {
                        this.generateCovers();
                    }
                }, 300);

            } catch (e) {
                this.error = 'Network error. Please try again.';
                this.showLanguageSelection = true;
            }

            this.generating = false;
        },

        async generateCovers() {
            if (!this.coverPrompt.trim()) return;
            this.coversLoading = true;
            this.coverError = '';

            const formData = new FormData();
            formData.append('prompt', this.coverPrompt);
            formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= generateCsrfToken() ?>');

            try {
                const response = await fetch('/admin/lead-magnets/ai-covers', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                if (!result.success) {
                    this.coverError = result.error || 'Failed to generate covers. Please try again.';
                    this.coversLoading = false;
                    return;
                }

                this.coverVariants = result.covers || [];
                // Auto-select first cover
                if (this.coverVariants.length > 0) {
                    this.selectCover(0);
                }

            } catch (e) {
                this.coverError = 'Network error generating covers. Please try again.';
            }

            this.coversLoading = false;
        },

        async uploadOwnCover(file) {
            if (!file) return;
            this.coverError = '';
            this.uploadedCoverPreview = URL.createObjectURL(file);

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
                    this.uploadedCoverPreview = '';
                    return;
                }

                this.coverImagePath = result.cover_image_path;
                this.coverImageUrl = result.cover_image_url;
                this.uploadedCoverPreview = result.cover_image_url;
                this.selectedCoverIndex = -2; // -2 = uploaded

            } catch (e) {
                this.coverError = 'Network error. Please try again.';
                this.uploadedCoverPreview = '';
            }
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
