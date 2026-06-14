<?php
$pageTitle = 'Select Your Content';
$metaDescription = 'Choose the courses and ebooks included in your membership plan.';
$tenant = $tenant ?? currentTenant();
$membership = $membership ?? null;
$maxCourses = $maxCourses ?? 0;
$maxEbooks = $maxEbooks ?? 0;
$availableCourses = $availableCourses ?? [];
$availableEbooks = $availableEbooks ?? [];
$selectedCourseIds = $selectedCourseIds ?? [];
$selectedEbookIds = $selectedEbookIds ?? [];
ob_start();
?>

<section class="py-8 lg:py-12" x-data="contentSelection()">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <a href="/membership/dashboard" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Select Your Content</h1>
            <p class="text-gray-500">Choose the courses and ebooks you want to access with your <strong class="text-brand"><?= h($membership['plan_name'] ?? 'membership') ?></strong> plan.</p>
        </div>

        <!-- Limits indicator -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-8">
                <?php if ($maxCourses !== null): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                <span x-text="selectedCourses.length" class="text-brand font-bold"></span> / <?= (int)$maxCourses ?> courses selected
                            </div>
                            <div class="text-xs text-gray-500">You can select up to <?= (int)$maxCourses ?> course<?= (int)$maxCourses > 1 ? 's' : '' ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($maxEbooks !== null): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                <span x-text="selectedEbooks.length" class="text-brand font-bold"></span> / <?= (int)$maxEbooks ?> ebooks selected
                            </div>
                            <div class="text-xs text-gray-500">You can select up to <?= (int)$maxEbooks ?> ebook<?= (int)$maxEbooks > 1 ? 's' : '' ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <form action="/membership/content-selection" method="POST" @submit="handleSubmit($event)">
            <?= csrfField() ?>

            <!-- Courses Section -->
            <?php if ($maxCourses !== null && !empty($availableCourses)): ?>
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-1">Courses</h2>
                    <p class="text-sm text-gray-500 mb-4">Select up to <?= (int)$maxCourses ?> course<?= (int)$maxCourses > 1 ? 's' : '' ?> to include in your membership.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($availableCourses as $course): ?>
                            <?php $isSelected = in_array((int)$course['id'], $selectedCourseIds); ?>
                            <label class="relative cursor-pointer group"
                                   :class="selectedCourses.includes(<?= (int)$course['id'] ?>) ? 'ring-2 ring-brand' : ''"
                                   x-data="{ get isDisabled() { return !selectedCourses.includes(<?= (int)$course['id'] ?>) && selectedCourses.length >= <?= (int)$maxCourses ?> } }">
                                <input type="checkbox" name="courses[]" value="<?= (int)$course['id'] ?>"
                                       class="sr-only"
                                       <?= $isSelected ? 'checked' : '' ?>
                                       :disabled="isDisabled"
                                       @change="toggleCourse(<?= (int)$course['id'] ?>, $event.target.checked)">

                                <div class="bg-white rounded-xl border-2 overflow-hidden transition-all"
                                     :class="selectedCourses.includes(<?= (int)$course['id'] ?>) ? 'border-brand shadow-md' : isDisabled ? 'border-gray-100 opacity-50' : 'border-gray-200 hover:border-gray-300 hover:shadow-sm'">

                                    <!-- Thumbnail -->
                                    <?php if (!empty($course['thumbnail_url'])): ?>
                                        <div class="aspect-video bg-gray-100 overflow-hidden">
                                            <img src="<?= h(imageUrl($course['thumbnail_url'])) ?>" alt="<?= h($course['title']) ?>" class="w-full h-full object-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="aspect-video bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="font-semibold text-gray-900 text-sm leading-snug"><?= h($course['title']) ?></h3>
                                            <!-- Checkbox indicator -->
                                            <div class="flex-shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center mt-0.5 transition-colors"
                                                 :class="selectedCourses.includes(<?= (int)$course['id'] ?>) ? 'bg-brand border-brand' : 'border-gray-300'">
                                                <svg x-show="selectedCourses.includes(<?= (int)$course['id'] ?>)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                        <?php if (!empty($course['description'])): ?>
                                            <p class="text-xs text-gray-500 mt-1.5 line-clamp-2"><?= h(truncate(strip_tags($course['description']), 100)) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($availableCourses)): ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                            <p class="text-gray-500 text-sm">No courses available for your tier level.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Ebooks Section -->
            <?php if ($maxEbooks !== null && !empty($availableEbooks)): ?>
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-1">Ebooks</h2>
                    <p class="text-sm text-gray-500 mb-4">Select up to <?= (int)$maxEbooks ?> ebook<?= (int)$maxEbooks > 1 ? 's' : '' ?> to include in your membership.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($availableEbooks as $ebook): ?>
                            <?php $isSelected = in_array((int)$ebook['id'], $selectedEbookIds); ?>
                            <label class="relative cursor-pointer group"
                                   :class="selectedEbooks.includes(<?= (int)$ebook['id'] ?>) ? 'ring-2 ring-brand' : ''"
                                   x-data="{ get isDisabled() { return !selectedEbooks.includes(<?= (int)$ebook['id'] ?>) && selectedEbooks.length >= <?= (int)$maxEbooks ?> } }">
                                <input type="checkbox" name="ebooks[]" value="<?= (int)$ebook['id'] ?>"
                                       class="sr-only"
                                       <?= $isSelected ? 'checked' : '' ?>
                                       :disabled="isDisabled"
                                       @change="toggleEbook(<?= (int)$ebook['id'] ?>, $event.target.checked)">

                                <div class="bg-white rounded-xl border-2 overflow-hidden transition-all"
                                     :class="selectedEbooks.includes(<?= (int)$ebook['id'] ?>) ? 'border-brand shadow-md' : isDisabled ? 'border-gray-100 opacity-50' : 'border-gray-200 hover:border-gray-300 hover:shadow-sm'">

                                    <!-- Thumbnail -->
                                    <?php if (!empty($ebook['cover_image_url'])): ?>
                                        <div class="aspect-[3/4] bg-gray-100 overflow-hidden max-h-48">
                                            <img src="<?= h(imageUrl($ebook['cover_image_url'])) ?>" alt="<?= h($ebook['title']) ?>" class="w-full h-full object-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="h-32 bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="font-semibold text-gray-900 text-sm leading-snug"><?= h($ebook['title']) ?></h3>
                                            <!-- Checkbox indicator -->
                                            <div class="flex-shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center mt-0.5 transition-colors"
                                                 :class="selectedEbooks.includes(<?= (int)$ebook['id'] ?>) ? 'bg-brand border-brand' : 'border-gray-300'">
                                                <svg x-show="selectedEbooks.includes(<?= (int)$ebook['id'] ?>)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                        <?php if (!empty($ebook['description'])): ?>
                                            <p class="text-xs text-gray-500 mt-1.5 line-clamp-2"><?= h(truncate(strip_tags($ebook['description']), 100)) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($availableEbooks)): ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                            <p class="text-gray-500 text-sm">No ebooks available for your tier level.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Submit -->
            <div class="sticky bottom-0 bg-gray-50/90 backdrop-blur-sm border-t border-gray-200 -mx-4 px-4 py-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 max-w-5xl mx-auto">
                    <div class="text-sm text-gray-500">
                        <span x-show="hasChanges" x-cloak class="text-amber-600 font-medium">You have unsaved changes.</span>
                        <span x-show="!hasChanges">Your current selections are saved.</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/membership/dashboard" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-sm transition">
                            Cancel
                        </a>
                        <button type="submit" class="btn-brand px-6 py-2.5 text-white font-semibold rounded-lg transition text-sm disabled:opacity-50"
                                :disabled="submitting">
                            <span x-show="!submitting">Save Selections</span>
                            <span x-show="submitting" x-cloak>Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</section>

<script>
function contentSelection() {
    return {
        selectedCourses: <?= json_encode(array_map('intval', $selectedCourseIds)) ?>,
        selectedEbooks: <?= json_encode(array_map('intval', $selectedEbookIds)) ?>,
        initialCourses: <?= json_encode(array_map('intval', $selectedCourseIds)) ?>,
        initialEbooks: <?= json_encode(array_map('intval', $selectedEbookIds)) ?>,
        maxCourses: <?= (int)$maxCourses ?>,
        maxEbooks: <?= (int)$maxEbooks ?>,
        submitting: false,

        get hasChanges() {
            const coursesChanged = JSON.stringify([...this.selectedCourses].sort()) !== JSON.stringify([...this.initialCourses].sort());
            const ebooksChanged = JSON.stringify([...this.selectedEbooks].sort()) !== JSON.stringify([...this.initialEbooks].sort());
            return coursesChanged || ebooksChanged;
        },

        toggleCourse(id, checked) {
            if (checked) {
                if (this.selectedCourses.length < this.maxCourses) {
                    this.selectedCourses.push(id);
                }
            } else {
                this.selectedCourses = this.selectedCourses.filter(c => c !== id);
            }
        },

        toggleEbook(id, checked) {
            if (checked) {
                if (this.selectedEbooks.length < this.maxEbooks) {
                    this.selectedEbooks.push(id);
                }
            } else {
                this.selectedEbooks = this.selectedEbooks.filter(e => e !== id);
            }
        },

        handleSubmit(event) {
            this.submitting = true;

            // Ensure all selected checkboxes are properly checked before submission
            const form = event.target;

            // Uncheck all course/ebook checkboxes first, then re-check selected ones
            form.querySelectorAll('input[name="courses[]"]').forEach(cb => {
                cb.checked = this.selectedCourses.includes(parseInt(cb.value));
                cb.disabled = false; // Re-enable so they submit
            });

            form.querySelectorAll('input[name="ebooks[]"]').forEach(cb => {
                cb.checked = this.selectedEbooks.includes(parseInt(cb.value));
                cb.disabled = false; // Re-enable so they submit
            });
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
