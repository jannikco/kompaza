<?php
/**
 * Homepage Editor View
 * Alpine.js-powered editor for homepage sections, template, and hero CTAs
 */
$sectionsJson = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$heroJson = json_encode($heroConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<div x-data="homepageEditor()" x-init="init()">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Homepage Editor</h2>
            <p class="text-sm text-gray-500 mt-1">Customize your homepage template, hero CTAs, and content sections.</p>
        </div>
        <a href="/" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Preview Site
        </a>
    </div>

    <form method="POST" action="/admin/homepage/update" x-ref="mainForm" @submit.prevent="serializeConfig(); $refs.mainForm.submit()">
        <?= csrfField() ?>
        <input type="hidden" name="homepage_template" :value="selectedTemplate">
        <input type="hidden" name="homepage_sections_json" x-ref="configJson">

        <!-- Template Picker -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                Template
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Starter -->
                <label class="relative cursor-pointer" @click="selectedTemplate = 'starter'">
                    <input type="radio" name="_template" value="starter" x-model="selectedTemplate" class="sr-only">
                    <div class="border-2 rounded-xl p-4 transition-all"
                         :class="selectedTemplate === 'starter' ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-300 hover:border-gray-500'">
                        <div class="bg-gray-900 rounded-lg p-3 mb-3 aspect-[16/10] flex flex-col items-center justify-center">
                            <div class="w-16 h-1.5 bg-gray-600 rounded mb-1.5"></div>
                            <div class="w-24 h-1 bg-gray-700 rounded mb-3"></div>
                            <div class="flex gap-1.5">
                                <div class="w-8 h-2 bg-indigo-500 rounded-sm"></div>
                                <div class="w-8 h-2 bg-gray-600 rounded-sm"></div>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <div class="w-10 h-6 bg-gray-700 rounded-sm"></div>
                                <div class="w-10 h-6 bg-gray-700 rounded-sm"></div>
                                <div class="w-10 h-6 bg-gray-700 rounded-sm"></div>
                            </div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">Starter</div>
                        <div class="text-xs text-gray-500 mt-0.5">Clean, centered layout. The classic default.</div>
                    </div>
                </label>

                <!-- Bold -->
                <label class="relative cursor-pointer" @click="selectedTemplate = 'bold'">
                    <input type="radio" name="_template" value="bold" x-model="selectedTemplate" class="sr-only">
                    <div class="border-2 rounded-xl p-4 transition-all"
                         :class="selectedTemplate === 'bold' ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-300 hover:border-gray-500'">
                        <div class="bg-gray-900 rounded-lg p-3 mb-3 aspect-[16/10] flex flex-col">
                            <div class="flex-1 bg-gradient-to-br from-indigo-500 to-sky-500 rounded-md flex items-center justify-center mb-2">
                                <div class="text-center">
                                    <div class="w-14 h-1 bg-white/80 rounded mx-auto mb-1"></div>
                                    <div class="w-10 h-0.5 bg-white/50 rounded mx-auto"></div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <div class="w-10 h-5 bg-gray-700 rounded-sm"></div>
                                <div class="w-10 h-5 bg-gray-700 rounded-sm"></div>
                                <div class="w-10 h-5 bg-gray-700 rounded-sm"></div>
                            </div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">Bold</div>
                        <div class="text-xs text-gray-500 mt-0.5">Gradient hero, modern SaaS aesthetic.</div>
                    </div>
                </label>

                <!-- Elegant -->
                <label class="relative cursor-pointer" @click="selectedTemplate = 'elegant'">
                    <input type="radio" name="_template" value="elegant" x-model="selectedTemplate" class="sr-only">
                    <div class="border-2 rounded-xl p-4 transition-all"
                         :class="selectedTemplate === 'elegant' ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-300 hover:border-gray-500'">
                        <div class="bg-gray-900 rounded-lg p-3 mb-3 aspect-[16/10] flex flex-col">
                            <div class="flex-1 flex gap-2 mb-2">
                                <div class="flex-1 flex flex-col justify-center">
                                    <div class="w-12 h-1 bg-gray-500 rounded mb-1"></div>
                                    <div class="w-16 h-1.5 bg-gray-400 rounded mb-1"></div>
                                    <div class="w-8 h-0.5 bg-gray-600 rounded"></div>
                                </div>
                                <div class="w-12 bg-gradient-to-br from-indigo-500/30 to-sky-500/30 rounded-md"></div>
                            </div>
                            <div class="flex gap-2">
                                <div class="w-10 h-5 bg-gray-700 rounded-sm border border-gray-300"></div>
                                <div class="w-10 h-5 bg-gray-700 rounded-sm border border-gray-300"></div>
                                <div class="w-10 h-5 bg-gray-700 rounded-sm border border-gray-300"></div>
                            </div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">Elegant</div>
                        <div class="text-xs text-gray-500 mt-0.5">Split hero, refined editorial feel.</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Hero CTA Configuration -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                Hero Call-to-Action Buttons
            </h3>
            <p class="text-sm text-gray-500 mb-4">Customize the buttons displayed in your hero section. Leave empty to hide a button.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Button Text</label>
                    <input type="text" x-model="hero.cta_primary_text"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. Browse Products">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Button URL</label>
                    <input type="text" x-model="hero.cta_primary_url"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. /produkter">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Button Text</label>
                    <input type="text" x-model="hero.cta_secondary_text"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. Read Our Blog">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Button URL</label>
                    <input type="text" x-model="hero.cta_secondary_url"
                           class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="e.g. /blog">
                </div>
            </div>
        </div>

        <!-- Sections Editor -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Sections
            </h3>
            <p class="text-sm text-gray-500 mb-4">Reorder, show/hide, and customize each section of your homepage.</p>

            <div class="space-y-3">
                <template x-for="(sec, index) in sections" :key="sec.id">
                    <div class="border border-gray-200 rounded-lg overflow-hidden" :class="sec.enabled ? '' : 'opacity-60'">
                        <!-- Section header -->
                        <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                            <!-- Move buttons -->
                            <div class="flex flex-col gap-0.5">
                                <button type="button" @click="moveUp(index)" :disabled="index === 0"
                                        class="p-0.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" @click="moveDown(index)" :disabled="index === sections.length - 1"
                                        class="p-0.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <!-- Type badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="sectionBadgeClass(sec.type)" x-text="sectionLabel(sec.type)"></span>

                            <!-- Heading preview -->
                            <span class="text-sm text-gray-700 font-medium truncate flex-1" x-text="sec.heading || sectionLabel(sec.type)"></span>

                            <!-- Toggle -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="sec.enabled" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>

                            <!-- Expand/collapse -->
                            <button type="button" @click="sec._expanded = !sec._expanded"
                                    class="p-1 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5 transition-transform" :class="sec._expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <!-- Delete (richtext only) -->
                            <template x-if="sec.type === 'richtext'">
                                <button type="button" @click="removeSection(index)"
                                        class="p-1 text-red-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </template>
                        </div>

                        <!-- Section details (expanded) -->
                        <div x-show="sec._expanded" x-collapse class="px-4 py-4 border-t border-gray-200 space-y-4">
                            <!-- Heading -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heading</label>
                                <input type="text" x-model="sec.heading"
                                       class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="Section heading">
                            </div>

                            <!-- Subtitle (for sections that use it) -->
                            <template x-if="['articles', 'ebooks', 'products', 'courses', 'newsletter'].includes(sec.type)">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" x-model="sec.subtitle"
                                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           placeholder="Optional subtitle text">
                                </div>
                            </template>

                            <!-- Count (for content sections) -->
                            <template x-if="['articles', 'ebooks', 'products', 'courses'].includes(sec.type)">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Items to Show</label>
                                    <select x-model.number="sec.count"
                                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="6">6</option>
                                        <option value="9">9</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                            </template>

                            <!-- Richtext body -->
                            <template x-if="sec.type === 'richtext'">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                    <div x-ref="quillContainer" class="quill-wrap">
                                        <div :id="'quill-' + sec.id" class="quill-editor" style="min-height: 200px;"></div>
                                    </div>
                                    <template x-effect="
                                        if (sec._expanded && sec.type === 'richtext' && !sec._quillInit) {
                                            sec._quillInit = true;
                                            $nextTick(() => {
                                                const el = document.getElementById('quill-' + sec.id);
                                                if (el && !el.__quill) {
                                                    const q = new Quill(el, {
                                                        theme: 'snow',
                                                        modules: {
                                                            toolbar: [
                                                                [{ header: [2, 3, false] }],
                                                                ['bold', 'italic', 'underline'],
                                                                [{ list: 'ordered' }, { list: 'bullet' }],
                                                                ['link'],
                                                                ['clean']
                                                            ]
                                                        }
                                                    });
                                                    q.root.innerHTML = sec.body || '';
                                                    q.on('text-change', () => { sec.body = q.root.innerHTML; });
                                                    el.__quill = q;
                                                }
                                            });
                                        }
                                    "></template>
                                </div>
                            </template>

                            <!-- Info for trust_strip -->
                            <template x-if="sec.type === 'trust_strip'">
                                <p class="text-sm text-gray-500">Shows content count statistics. Best with the Bold template.</p>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Add Section -->
            <div class="mt-4" x-data="{ addOpen: false }">
                <button type="button" @click="addOpen = !addOpen"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Section
                </button>
                <div x-show="addOpen" x-cloak class="mt-2 flex flex-wrap gap-2">
                    <button type="button" @click="addRichtext(); addOpen = false"
                            class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Rich Text Block
                    </button>
                    <button type="button" @click="addTrustStrip(); addOpen = false"
                            class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            x-show="!sections.some(s => s.type === 'trust_strip')">
                        Trust Strip
                    </button>
                </div>
                <template x-if="richtextCount >= 10">
                    <p class="text-xs text-gray-400 mt-2">Maximum of 10 rich text sections reached.</p>
                </template>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-4">
            <a href="/" target="_blank" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Preview
            </a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Save Homepage
            </button>
        </div>
    </form>
</div>

<script>
function homepageEditor() {
    return {
        selectedTemplate: '<?= h($template) ?>',
        hero: <?= $heroJson ?>,
        sections: [],
        _rawSections: <?= $sectionsJson ?>,

        init() {
            // Add UI-only properties
            this.sections = this._rawSections.map(s => ({
                ...s,
                _expanded: false,
                _quillInit: false
            }));
        },

        get richtextCount() {
            return this.sections.filter(s => s.type === 'richtext').length;
        },

        moveUp(index) {
            if (index <= 0) return;
            const temp = this.sections[index];
            this.sections.splice(index, 1);
            this.sections.splice(index - 1, 0, temp);
        },

        moveDown(index) {
            if (index >= this.sections.length - 1) return;
            const temp = this.sections[index];
            this.sections.splice(index, 1);
            this.sections.splice(index + 1, 0, temp);
        },

        removeSection(index) {
            if (this.sections[index].type !== 'richtext') return;
            this.sections.splice(index, 1);
        },

        addRichtext() {
            if (this.richtextCount >= 10) return;
            this.sections.push({
                id: 'sec_' + Math.random().toString(36).substr(2, 8),
                type: 'richtext',
                enabled: true,
                heading: 'New Section',
                body: '',
                _expanded: true,
                _quillInit: false
            });
        },

        addTrustStrip() {
            if (this.sections.some(s => s.type === 'trust_strip')) return;
            this.sections.splice(0, 0, {
                id: 'sec_' + Math.random().toString(36).substr(2, 8),
                type: 'trust_strip',
                enabled: true,
                heading: 'Trust Strip',
                _expanded: false,
                _quillInit: false
            });
        },

        sectionLabel(type) {
            const labels = {
                articles: 'Articles',
                ebooks: 'Ebooks',
                products: 'Products',
                courses: 'Courses',
                newsletter: 'Newsletter',
                richtext: 'Rich Text',
                trust_strip: 'Trust Strip'
            };
            return labels[type] || type;
        },

        sectionBadgeClass(type) {
            const classes = {
                articles: 'bg-blue-100 text-blue-800',
                ebooks: 'bg-purple-100 text-purple-800',
                products: 'bg-green-100 text-green-800',
                courses: 'bg-yellow-100 text-yellow-800',
                newsletter: 'bg-pink-100 text-pink-800',
                richtext: 'bg-gray-100 text-gray-800',
                trust_strip: 'bg-indigo-100 text-indigo-800'
            };
            return classes[type] || 'bg-gray-100 text-gray-800';
        },

        serializeConfig() {
            // Strip UI-only properties before serializing
            const cleanSections = this.sections.map(s => {
                const clean = { id: s.id, type: s.type, enabled: s.enabled, heading: s.heading };
                if (s.subtitle !== undefined) clean.subtitle = s.subtitle;
                if (s.count !== undefined) clean.count = s.count;
                if (s.type === 'richtext') clean.body = s.body || '';
                return clean;
            });
            const config = {
                hero: this.hero,
                sections: cleanSections
            };
            this.$refs.configJson.value = JSON.stringify(config);
        }
    };
}
</script>
