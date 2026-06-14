<?php
$pageTitle = 'Membership Dashboard';
$metaDescription = 'Manage your membership, access courses, ebooks, and live sessions.';
$tenant = $tenant ?? currentTenant();
$currency = $tenant['currency'] ?? 'DKK';
$membership = $membership ?? null;
$enrollments = $enrollments ?? [];
$courseSelections = $courseSelections ?? [];
$ebookSelections = $ebookSelections ?? [];
$upcomingSessions = $upcomingSessions ?? [];
$registeredSessionIds = $registeredSessionIds ?? [];
$user = currentUser();
ob_start();
?>

<?php if (!$membership): ?>

    <!-- No membership CTA -->
    <section class="py-16 lg:py-24">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-2xl border border-gray-200 p-8 lg:p-12">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-3">You don't have an active membership</h1>
                <p class="text-gray-500 mb-8">Unlock exclusive courses, ebooks, community access, and more by choosing a membership plan.</p>
                <a href="/membership" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-lg transition text-sm">
                    View Membership Plans
                </a>
            </div>
        </div>
    </section>

<?php else: ?>

    <section class="py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Welcome Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Welcome back, <?= h($user['name'] ?? 'Member') ?>
                    </h1>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="inline-flex items-center rounded-full bg-brand/10 px-3 py-1 text-sm font-semibold text-brand">
                            <?= h($membership['plan_name']) ?>
                        </span>
                        <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-700',
                                'trialing' => 'bg-blue-100 text-blue-700',
                                'past_due' => 'bg-yellow-100 text-yellow-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                            $statusColor = $statusColors[$membership['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="inline-flex items-center rounded-full <?= $statusColor ?> px-3 py-1 text-xs font-medium capitalize">
                            <?= h($membership['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center gap-3 flex-wrap">
                    <?php if ($membership['max_courses'] !== null || $membership['max_ebooks'] !== null): ?>
                        <a href="/membership/content-selection" class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-sm transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Select Content
                        </a>
                    <?php endif; ?>
                    <form action="/membership/portal" method="POST" class="inline">
                        <?= csrfField() ?>
                        <button type="submit" class="inline-flex items-center px-4 py-2.5 btn-brand rounded-lg text-white font-medium text-sm transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Manage Billing
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="text-sm text-gray-500 mb-1">Plan Tier</div>
                    <div class="text-xl font-bold text-gray-900"><?= h($membership['plan_name']) ?></div>
                    <div class="text-xs text-gray-400 mt-1">Tier Level <?= (int)$membership['tier_level'] ?></div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="text-sm text-gray-500 mb-1">Billing</div>
                    <div class="text-xl font-bold text-gray-900 capitalize"><?= h($membership['billing_interval']) ?></div>
                    <div class="text-xs text-gray-400 mt-1">
                        Status: <span class="capitalize"><?= h($membership['status']) ?></span>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="text-sm text-gray-500 mb-1">Current Period Ends</div>
                    <div class="text-xl font-bold text-gray-900">
                        <?= $membership['current_period_end'] ? formatDate($membership['current_period_end'], 'd M Y') : 'N/A' ?>
                    </div>
                    <?php if ($membership['current_period_end'] && strtotime($membership['current_period_end']) > time()): ?>
                        <?php $daysLeft = (int)ceil((strtotime($membership['current_period_end']) - time()) / 86400); ?>
                        <div class="text-xs text-gray-400 mt-1"><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> remaining</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Courses -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">My Courses</h2>
                    <a href="/courses" class="text-sm text-brand hover:underline font-medium">Browse All</a>
                </div>

                <?php if (empty($enrollments)): ?>
                    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <p class="text-gray-500 text-sm mb-3">You are not enrolled in any courses yet.</p>
                        <?php if ($membership['max_courses'] !== null): ?>
                            <a href="/membership/content-selection" class="text-brand text-sm font-medium hover:underline">Select your courses</a>
                        <?php else: ?>
                            <a href="/courses" class="text-brand text-sm font-medium hover:underline">Browse courses</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <?php
                                $progress = 0;
                                if ((int)($enrollment['total_lessons'] ?? 0) > 0) {
                                    $progress = round(((int)($enrollment['completed_lessons'] ?? 0) / (int)$enrollment['total_lessons']) * 100);
                                }
                            ?>
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition group">
                                <?php if (!empty($enrollment['thumbnail_url'])): ?>
                                    <div class="aspect-video bg-gray-100 overflow-hidden">
                                        <img src="<?= h(imageUrl($enrollment['thumbnail_url'])) ?>" alt="<?= h($enrollment['course_title'] ?? $enrollment['title'] ?? '') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                <?php else: ?>
                                    <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                <?php endif; ?>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 text-sm mb-2 truncate"><?= h($enrollment['course_title'] ?? $enrollment['title'] ?? 'Course') ?></h3>
                                    <!-- Progress bar -->
                                    <div class="mb-3">
                                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                            <span>Progress</span>
                                            <span><?= $progress ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-brand rounded-full h-2 transition-all duration-300" style="width: <?= $progress ?>%"></div>
                                        </div>
                                    </div>
                                    <a href="/course/<?= (int)($enrollment['course_id'] ?? $enrollment['id'] ?? 0) ?>/learn" class="inline-flex items-center text-sm font-medium text-brand hover:underline">
                                        <?= $progress > 0 ? 'Continue' : 'Start' ?> Learning
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- My Ebooks -->
            <?php if (!empty($ebookSelections)): ?>
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">My Ebooks</h2>
                        <a href="/eboger" class="text-sm text-brand hover:underline font-medium">Browse All</a>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                        <?php foreach ($ebookSelections as $selection): ?>
                            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg bg-brand/10 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 truncate">Ebook #<?= (int)$selection['content_id'] ?></span>
                                </div>
                                <a href="/ebog/<?= (int)$selection['content_id'] ?>" class="text-sm text-brand hover:underline font-medium flex-shrink-0 ml-4">Read</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upcoming Live Sessions -->
            <?php if ($membership['can_access_live_qa']): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Upcoming Live Sessions</h2>

                    <?php if (empty($upcomingSessions)): ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <p class="text-gray-500 text-sm">No upcoming live sessions scheduled.</p>
                            <p class="text-gray-400 text-xs mt-1">Check back soon for new sessions.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($upcomingSessions as $session): ?>
                                <?php $isRegistered = in_array((int)$session['id'], $registeredSessionIds); ?>
                                <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="w-14 h-14 rounded-xl bg-brand/10 flex flex-col items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-brand uppercase"><?= date('M', strtotime($session['scheduled_at'])) ?></span>
                                            <span class="text-lg font-extrabold text-brand leading-none"><?= date('d', strtotime($session['scheduled_at'])) ?></span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 text-sm"><?= h($session['title']) ?></h3>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <?= date('l, M d \a\t H:i', strtotime($session['scheduled_at'])) ?>
                                                <?php if ($session['duration_minutes']): ?>
                                                    &middot; <?= (int)$session['duration_minutes'] ?> min
                                                <?php endif; ?>
                                            </p>
                                            <?php if ($session['description']): ?>
                                                <p class="text-xs text-gray-400 mt-1"><?= h(truncate($session['description'], 120)) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <?php if ($isRegistered): ?>
                                            <?php if ($session['status'] === 'live' && $session['meeting_url']): ?>
                                                <a href="<?= h($session['meeting_url']) ?>" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center px-4 py-2 btn-brand rounded-lg text-white font-medium text-sm transition">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    Join Now
                                                </a>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Registered
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <form action="/membership/live-session/register" method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-brand text-brand rounded-lg hover:bg-brand hover:text-white font-medium text-sm transition">
                                                    Register
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Links</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <?php if ($membership['can_access_prompts']): ?>
                        <a href="/prompts" class="bg-white rounded-xl border border-gray-200 p-5 text-center hover:shadow-md hover:border-brand/30 transition group">
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-brand transition">Prompts</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($membership['can_post_community'] || !$membership['community_read_only']): ?>
                        <a href="/community" class="bg-white rounded-xl border border-gray-200 p-5 text-center hover:shadow-md hover:border-brand/30 transition group">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-brand transition">Community</span>
                        </a>
                    <?php endif; ?>

                    <a href="/courses" class="bg-white rounded-xl border border-gray-200 p-5 text-center hover:shadow-md hover:border-brand/30 transition group">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-brand transition">Courses</span>
                    </a>

                    <a href="/eboger" class="bg-white rounded-xl border border-gray-200 p-5 text-center hover:shadow-md hover:border-brand/30 transition group">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-brand transition">Ebooks</span>
                    </a>
                </div>
            </div>

        </div>
    </section>

<?php endif; ?>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
