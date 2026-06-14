<?php
$pageTitle = 'Live Q&A Sessions';
$metaDescription = 'Join live Q&A sessions with experts and get your questions answered in real-time.';
$tenant = $tenant ?? currentTenant();

$upcomingSessions = $upcomingSessions ?? [];
$pastSessions = $pastSessions ?? [];
$userTierLevel = $userTierLevel ?? 0;
$registeredSessionIds = $registeredSessionIds ?? [];

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Live Q&A Sessions</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Join live sessions to ask questions, learn from experts, and connect with the community.</p>
        </div>

        <!-- Upcoming Sessions -->
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Upcoming Sessions</h2>

            <?php if (empty($upcomingSessions)): ?>
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <svg class="w-14 h-14 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-gray-500 text-lg">No upcoming sessions scheduled.</p>
                    <p class="text-gray-400 text-sm mt-1">Check back soon for new sessions.</p>
                </div>
            <?php else: ?>
                <div class="space-y-5">
                    <?php foreach ($upcomingSessions as $session): ?>
                        <?php
                            $hasAccess = $userTierLevel >= (int)($session['min_tier_level'] ?? 0);
                            $isRegistered = in_array((int)$session['id'], $registeredSessionIds);
                            $sessionTime = strtotime($session['scheduled_at'] ?? '');
                            $isLive = $sessionTime && $sessionTime <= time() && ($sessionTime + ((int)($session['duration_minutes'] ?? 60) * 60)) >= time();

                            $tierLabel = 'Free';
                            $tierClasses = 'bg-green-100 text-green-700';
                            if ((int)($session['min_tier_level'] ?? 0) === 2) {
                                $tierLabel = 'Pro';
                                $tierClasses = 'bg-blue-100 text-blue-700';
                            } elseif ((int)($session['min_tier_level'] ?? 0) >= 3) {
                                $tierLabel = 'Premium';
                                $tierClasses = 'bg-purple-100 text-purple-700';
                            } elseif ((int)($session['min_tier_level'] ?? 0) === 1) {
                                $tierLabel = 'Basic';
                                $tierClasses = 'bg-gray-100 text-gray-700';
                            }
                        ?>
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <div class="flex flex-col sm:flex-row sm:items-start gap-5">
                                    <!-- Date Badge -->
                                    <div class="flex-shrink-0 w-16 text-center">
                                        <div class="bg-brand/10 rounded-xl py-3 px-2">
                                            <div class="text-xs font-semibold text-brand uppercase"><?= $sessionTime ? date('M', $sessionTime) : '' ?></div>
                                            <div class="text-2xl font-bold text-gray-900"><?= $sessionTime ? date('d', $sessionTime) : '' ?></div>
                                            <div class="text-xs text-gray-500"><?= $sessionTime ? date('D', $sessionTime) : '' ?></div>
                                        </div>
                                    </div>

                                    <!-- Session Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <?php if ($isLive): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5 animate-pulse"></span>
                                                    Live Now
                                                </span>
                                            <?php endif; ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tierClasses ?>"><?= $tierLabel ?></span>
                                        </div>

                                        <h3 class="text-lg font-semibold text-gray-900 mb-1"><?= h($session['title']) ?></h3>

                                        <?php if (!empty($session['description'])): ?>
                                            <p class="text-sm text-gray-500 leading-relaxed mb-3 line-clamp-2"><?= h($session['description']) ?></p>
                                        <?php endif; ?>

                                        <!-- Meta Info -->
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400">
                                            <span class="inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <?= $sessionTime ? date('H:i', $sessionTime) : 'TBA' ?>
                                            </span>
                                            <?php if (!empty($session['duration_minutes'])): ?>
                                                <span class="inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    <?= (int)$session['duration_minutes'] ?> min
                                                </span>
                                            <?php endif; ?>
                                            <?php if (isset($session['registration_count'])): ?>
                                                <span class="inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <?= number_format((int)$session['registration_count']) ?> registered
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Action Column -->
                                    <div class="flex-shrink-0 sm:text-right">
                                        <?php if ($isRegistered): ?>
                                            <div class="space-y-2">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-green-50 text-green-700 border border-green-200">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Registered
                                                </span>
                                                <?php if ($isLive && !empty($session['meeting_url'])): ?>
                                                    <div>
                                                        <a href="<?= h($session['meeting_url']) ?>" target="_blank" rel="noopener"
                                                           class="inline-flex items-center px-4 py-2 btn-brand text-white text-sm font-semibold rounded-lg transition">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                            Join Session
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif (!isAuthenticated()): ?>
                                            <a href="/login?redirect=<?= urlencode('/live-qa') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                                Log in to register
                                            </a>
                                        <?php elseif (!$hasAccess): ?>
                                            <div class="text-center sm:text-right">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-50 text-gray-400 border border-gray-200">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    Upgrade to join
                                                </span>
                                                <a href="/membership" class="block mt-1.5 text-xs text-brand hover:underline font-medium">View plans</a>
                                            </div>
                                        <?php else: ?>
                                            <form method="POST" action="/live-qa/register">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                                <button type="submit" class="inline-flex items-center px-5 py-2.5 btn-brand text-white text-sm font-semibold rounded-lg transition">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                    Register
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Past Sessions -->
        <?php if (!empty($pastSessions)): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Past Sessions</h2>

                <div class="space-y-4">
                    <?php foreach ($pastSessions as $session): ?>
                        <?php
                            $sessionTime = strtotime($session['scheduled_at'] ?? '');
                            $isCancelled = ($session['status'] ?? '') === 'cancelled';
                            $hasRecording = !empty($session['recording_s3_key']);
                            $hasAccess = $userTierLevel >= (int)($session['min_tier_level'] ?? 0);

                            $statusLabel = 'Completed';
                            $statusClasses = 'bg-gray-100 text-gray-600';
                            if ($isCancelled) {
                                $statusLabel = 'Cancelled';
                                $statusClasses = 'bg-red-50 text-red-600';
                            }
                        ?>
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden opacity-80 hover:opacity-100 transition-opacity">
                            <div class="p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <!-- Date -->
                                    <div class="flex-shrink-0 text-sm text-gray-400 w-24">
                                        <?= $sessionTime ? date('d M Y', $sessionTime) : 'N/A' ?>
                                    </div>

                                    <!-- Session Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <h3 class="text-base font-semibold text-gray-700"><?= h($session['title']) ?></h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $statusClasses ?>"><?= $statusLabel ?></span>
                                        </div>
                                        <?php if (!empty($session['description'])): ?>
                                            <p class="text-sm text-gray-400 line-clamp-1"><?= h($session['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-gray-400">
                                            <?php if (!empty($session['duration_minutes'])): ?>
                                                <span><?= (int)$session['duration_minutes'] ?> min</span>
                                            <?php endif; ?>
                                            <?php if (isset($session['registration_count'])): ?>
                                                <span><?= number_format((int)$session['registration_count']) ?> attended</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Recording Link -->
                                    <div class="flex-shrink-0">
                                        <?php if ($hasRecording && $hasAccess): ?>
                                            <a href="/live-qa/recording/<?= (int)$session['id'] ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Watch Recording
                                            </a>
                                        <?php elseif ($hasRecording && !$hasAccess): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-400 bg-gray-50 rounded-lg border border-gray-200">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                Upgrade for recording
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Empty State: No sessions at all -->
        <?php if (empty($upcomingSessions) && empty($pastSessions)): ?>
            <div class="text-center py-8">
                <p class="text-gray-400 text-sm">No live sessions have been scheduled yet. Check back soon!</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
