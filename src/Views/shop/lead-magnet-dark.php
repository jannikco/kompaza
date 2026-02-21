<?php require __DIR__ . '/lead-magnet-setup.php'; ob_start(); ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap');

    .mono { font-family: 'JetBrains Mono', monospace; }
    .dark-bg { background: #0f172a; }
    .dark-alt-bg { background: #1e293b; }

    /* Glowing neon divider */
    .glow-divider {
        height: 1px;
        background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);
        box-shadow: 0 0 10px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3), 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1);
    }

    /* Terminal window */
    .terminal-window {
        background: rgba(15,23,42,0.95);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        overflow: hidden;
    }
    .terminal-titlebar {
        background: rgba(255,255,255,0.05);
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .terminal-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
    }

    /* Dark input with glow focus */
    .dark-input {
        background: rgba(255,255,255,0.06) !important;
        border: 1px solid rgba(255,255,255,0.15) !important;
        color: #fff !important;
        transition: border-color 0.3s, box-shadow 0.3s !important;
    }
    .dark-input::placeholder { color: #4b5563 !important; }
    .dark-input:focus {
        background: rgba(255,255,255,0.1) !important;
        border-color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>) !important;
        box-shadow: 0 0 0 3px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2), 0 0 15px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1) !important;
        outline: none !important;
    }

    /* Glow button */
    .btn-glow {
        box-shadow: 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4), 0 4px 12px rgba(0,0,0,0.3);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-glow:hover {
        transform: scale(1.02);
        box-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.6), 0 6px 20px rgba(0,0,0,0.4);
    }

    /* Neon stat glow */
    .neon-stat {
        color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);
        text-shadow: 0 0 20px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.5), 0 0 40px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);
        letter-spacing: 0.05em;
    }

    /* Progress bar glow */
    .progress-glow {
        box-shadow: 0 0 8px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4);
    }

    /* Chat bubble */
    .chat-bubble {
        position: relative;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 0 12px 12px 12px;
    }
    .chat-bubble::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 0;
        width: 0; height: 0;
        border-top: 8px solid rgba(255,255,255,0.1);
        border-left: 8px solid transparent;
    }

    /* Horizontal scroll strip */
    .scroll-strip { -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .scroll-strip::-webkit-scrollbar { display: none; }

    /* Tag cloud hover */
    .tag-pill {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        transition: all 0.2s ease;
    }
    .tag-pill:hover {
        background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);
        border-color: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4);
    }

    /* Floating orbs */
    @keyframes floatOrb1 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(30px, -20px); } }
    @keyframes floatOrb2 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-20px, 30px); } }

    /* Grid overlay */
    .grid-overlay {
        background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%2040%2040%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%200h40v40H0z%22%20fill%3D%22none%22%20stroke%3D%22%23fff%22%20stroke-width%3D%220.5%22%2F%3E%3C%2Fsvg%3E');
        background-size: 40px 40px;
    }
</style>

<!-- 1. Hero — Terminal-style form with fake title bar -->
<section class="dark-bg relative overflow-hidden" id="hero">
    <div class="absolute inset-0 grid-overlay opacity-[0.02]"></div>
    <div class="absolute top-10 right-20 w-72 h-72 rounded-full pointer-events-none opacity-20 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2); animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="absolute bottom-10 left-10 w-56 h-56 rounded-full pointer-events-none opacity-15 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15); animation: floatOrb2 25s ease-in-out infinite;"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <!-- Left: Text -->
            <div>
                <?php if ($heroBadge): ?>
                    <div class="mono inline-flex items-center text-sm font-medium mb-6 px-4 py-1.5 rounded-full" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>); background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.1); border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3);">
                        <span class="mr-2 opacity-60">&gt;_</span> <?= h($heroBadge) ?>
                    </div>
                <?php endif; ?>

                <?php if ($coverImage): ?>
                    <div class="mb-6">
                        <img src="<?= h($coverImage) ?>" alt="<?= h($leadMagnet['title']) ?>" class="w-36 h-auto rounded-lg" style="box-shadow: 0 0 30px rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);">
                    </div>
                <?php endif; ?>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                    <?php if (!empty($heroAccent)): ?>
                        <?= str_replace(h($heroAccent), '<span style="color: rgb(' . $r . ',' . $g . ',' . $b . '); text-shadow: 0 0 20px rgba(' . $r . ',' . $g . ',' . $b . ',0.5);">' . h($heroAccent) . '</span>', h($heroHeadline)) ?>
                    <?php else: ?>
                        <?= h($heroHeadline) ?>
                    <?php endif; ?>
                </h1>

                <?php if (!empty($leadMagnet['hero_subheadline'])): ?>
                    <p class="mt-5 text-lg text-gray-400 leading-relaxed"><?= h($leadMagnet['hero_subheadline']) ?></p>
                <?php elseif (!empty($leadMagnet['subtitle'])): ?>
                    <p class="mt-5 text-lg text-gray-400 leading-relaxed"><?= h($leadMagnet['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Right: Terminal form -->
            <div class="flex justify-center lg:justify-end">
                <div class="terminal-window w-full max-w-lg" id="signup-form" x-data="{ loading: false, error: '' }">
                    <div class="terminal-titlebar">
                        <div class="terminal-dot" style="background: #ef4444;"></div>
                        <div class="terminal-dot" style="background: #eab308;"></div>
                        <div class="terminal-dot" style="background: #22c55e;"></div>
                        <span class="mono text-xs text-gray-500 ml-2"><?= h($sh('form_title', 'Get Your Free Copy')) ?></span>
                    </div>
                    <div class="p-6 sm:p-8">
                        <p class="text-gray-400 text-sm mb-6"><?= h($sh('form_subtitle', 'Enter your details below and we\'ll send it straight to your inbox.')) ?></p>

                        <form action="/lp/tilmeld" method="POST"
                              @submit.prevent="
                                  loading = true; error = '';
                                  const fd = new FormData($el);
                                  fetch($el.action, { method: 'POST', body: fd })
                                      .then(r => r.json())
                                      .then(d => {
                                          loading = false;
                                          if (d.success) { window.location.href = '/lp/succes/<?= h($leadMagnet['slug']) ?>'; }
                                          else { error = d.message || 'Something went wrong. Please try again.'; }
                                      })
                                      .catch(() => { loading = false; error = 'Network error. Please try again.'; });
                              ">
                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="lead_magnet_id" value="<?= (int)$leadMagnet['id'] ?>">

                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1"><?= h($sh('form_name_label', 'Full Name')) ?></label>
                                    <input type="text" id="name" name="name" required class="dark-input w-full px-4 py-3 rounded-lg text-sm" placeholder="John Smith">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1"><?= h($sh('form_email_label', 'Email Address')) ?></label>
                                    <input type="email" id="email" name="email" required class="dark-input w-full px-4 py-3 rounded-lg text-sm" placeholder="john@company.com">
                                </div>
                            </div>

                            <div x-show="error" x-cloak class="mt-4 p-3 bg-red-900/30 border border-red-500/30 rounded-lg text-red-300 text-sm" x-text="error"></div>

                            <button type="submit" :disabled="loading"
                                    class="btn-glow mt-6 w-full px-6 py-3.5 text-white font-semibold rounded-lg text-base disabled:opacity-50"
                                    style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
                                <span x-show="!loading"><?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?></span>
                                <span x-show="loading" x-cloak><?= h($sh('form_sending', 'Sending...')) ?></span>
                            </button>

                            <div class="mt-4 flex items-center justify-center space-x-4 text-xs text-gray-500">
                                <span class="flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Secure</span>
                                </span>
                                <span class="text-gray-600">|</span>
                                <span>No spam, ever</span>
                                <span class="text-gray-600">|</span>
                                <span>Unsubscribe anytime</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Glow Divider -->
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>

<!-- 2. Social Proof — Dashboard metric cards with monospace values -->
<section class="dark-bg">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
        <div class="grid grid-cols-3 gap-4">
            <?php if (!empty($socialProof)): ?>
                <?php foreach ($socialProof as $proof): ?>
                    <div class="rounded-xl p-5 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                        <?php if (!empty($proof['icon'])): ?>
                            <div class="text-xl mb-1"><?= h($proof['icon']) ?></div>
                        <?php endif; ?>
                        <div class="mono text-2xl sm:text-3xl font-bold neon-stat"><?= h($proof['value'] ?? '') ?></div>
                        <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider"><?= h($proof['label'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="rounded-xl p-5 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                    <div class="mono text-2xl sm:text-3xl font-bold neon-stat"><?= h($sh('default_proof_1', 'PDF Guide')) ?></div>
                </div>
                <div class="rounded-xl p-5 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                    <div class="mono text-2xl sm:text-3xl font-bold neon-stat"><?= h($sh('default_proof_2', '100% Free')) ?></div>
                </div>
                <div class="rounded-xl p-5 text-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                    <div class="mono text-2xl sm:text-3xl font-bold neon-stat"><?= h($sh('default_proof_3', 'Instant Access')) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 3. Features — Horizontal scrolling card strip -->
<?php if (!empty($features)): ?>
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-alt-bg py-20 lg:py-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-12"><?= h($leadMagnet['features_headline'] ?? 'What\'s Inside') ?></h2>
        <div class="scroll-strip flex gap-6 overflow-x-auto pb-4 -mx-4 px-4">
            <?php foreach ($features as $feature): ?>
                <?php
                    $featureTitle = is_array($feature) ? ($feature['title'] ?? '') : $feature;
                    $featureDesc = is_array($feature) ? ($feature['description'] ?? '') : '';
                    $featureIcon = is_array($feature) ? ($feature['icon'] ?? null) : null;
                ?>
                <div class="flex-shrink-0 w-[280px] rounded-xl p-6" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-top: 3px solid rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.6);">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-4" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.12);">
                        <?php if ($featureIcon): ?>
                            <span class="text-lg" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h($featureIcon) ?></span>
                        <?php else: ?>
                            <svg class="w-5 h-5" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-semibold text-white mb-2"><?= h($featureTitle) ?></h3>
                    <?php if ($featureDesc): ?>
                        <p class="text-gray-400 text-sm leading-relaxed"><?= h($featureDesc) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 4. Chapters — Full-width rows with monospace number, title, and progress bar -->
<?php if (!empty($chapters)): ?>
<div class="<?= !empty($features) ? 'dark-alt-bg' : 'dark-bg' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-bg py-20 lg:py-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-4"><?= h($sh('toc_title', 'Table of Contents')) ?></h2>
        <p class="text-center text-gray-400 mb-12"><?= h($sh('toc_subtitle', 'A preview of what you\'ll find inside')) ?></p>
        <?php $totalChapters = count($chapters); ?>
        <div class="space-y-3 max-w-4xl mx-auto">
            <?php foreach ($chapters as $cIndex => $chapter): ?>
                <?php $progress = round((($cIndex + 1) / $totalChapters) * 100); ?>
                <div class="flex items-center gap-4 sm:gap-6 p-4 rounded-xl" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <span class="mono text-2xl sm:text-3xl font-bold flex-shrink-0 w-12 text-right" style="color: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.4);"><?= h($chapter['number'] ?? '') ?></span>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-white truncate"><?= h($chapter['title'] ?? '') ?></h3>
                        <?php if (!empty($chapter['description'])): ?>
                            <p class="text-gray-500 text-sm mt-0.5 truncate"><?= h($chapter['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="hidden sm:flex items-center gap-3 flex-shrink-0 w-32">
                        <div class="flex-1 h-2 rounded-full" style="background: rgba(255,255,255,0.06);">
                            <div class="h-2 rounded-full progress-glow" style="width: <?= $progress ?>%; background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"></div>
                        </div>
                        <span class="mono text-xs text-gray-500"><?= $progress ?>%</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA -->
<?php if (!empty($chapters)): ?>
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-16 lg:py-20 relative overflow-hidden" style="background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.03)), #0f172a;">
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_1', 'Don\'t Miss Out')) ?></h2>
        <p class="text-gray-400 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_1_sub', 'Get instant access to strategies that drive real results.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-glow inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg text-base"
           style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 5. Key Statistics — Neon monospace counters in horizontal flex -->
<?php if (!empty($keyStatistics)): ?>
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-alt-bg py-20 lg:py-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-14"><?= h($sh('stats_title', 'By the Numbers')) ?></h2>
        <div class="flex flex-wrap justify-center gap-12 lg:gap-16">
            <?php foreach ($keyStatistics as $stat): ?>
                <div class="text-center">
                    <div class="mono text-4xl sm:text-5xl lg:text-6xl font-bold neon-stat"><?= h($stat['value'] ?? '') ?></div>
                    <div class="text-sm text-gray-500 mt-2 uppercase tracking-wider"><?= h($stat['label'] ?? '') ?></div>
                    <div class="mt-2 h-0.5 w-full rounded-full progress-glow" style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Before/After — Terminal diff view -->
<?php if ($beforeAfter && (!empty($beforeAfter['before']) || !empty($beforeAfter['after']))): ?>
<div class="<?= !empty($keyStatistics) ? 'dark-alt-bg' : 'dark-bg' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-bg py-20 lg:py-28">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-12"><?= h($sh('transformation_title', 'The Transformation')) ?></h2>
        <div class="terminal-window max-w-3xl mx-auto">
            <div class="terminal-titlebar">
                <div class="terminal-dot" style="background: #ef4444;"></div>
                <div class="terminal-dot" style="background: #eab308;"></div>
                <div class="terminal-dot" style="background: #22c55e;"></div>
                <span class="mono text-xs text-gray-500 ml-2">diff --transformation</span>
            </div>
            <div class="p-5 sm:p-6 space-y-2 mono text-sm">
                <?php if (!empty($beforeAfter['before'])): ?>
                    <?php foreach ($beforeAfter['before'] as $item): ?>
                        <div class="flex items-start py-1.5 px-3 rounded" style="background: rgba(239,68,68,0.08);">
                            <span class="text-red-400 mr-3 flex-shrink-0 font-bold">-</span>
                            <span class="text-red-300"><?= h($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($beforeAfter['before']) && !empty($beforeAfter['after'])): ?>
                    <div class="border-t border-gray-700/50 my-3"></div>
                <?php endif; ?>
                <?php if (!empty($beforeAfter['after'])): ?>
                    <?php foreach ($beforeAfter['after'] as $item): ?>
                        <div class="flex items-start py-1.5 px-3 rounded" style="background: rgba(34,197,94,0.08);">
                            <span class="text-green-400 mr-3 flex-shrink-0 font-bold">+</span>
                            <span class="text-green-300"><?= h($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 7. Target Audience — Tag cloud pills with hover descriptions -->
<?php if (!empty($targetAudience)): ?>
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-alt-bg py-20 lg:py-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-12"><?= h($sh('audience_title', 'Who Is This For?')) ?></h2>
        <div class="flex flex-wrap justify-center gap-3 max-w-4xl mx-auto" x-data="{ active: null }">
            <?php foreach ($targetAudience as $pIndex => $persona): ?>
                <div class="relative" @mouseenter="active = <?= $pIndex ?>" @mouseleave="active = null">
                    <div class="tag-pill rounded-full px-5 py-2.5 cursor-default">
                        <?php if (!empty($persona['icon'])): ?>
                            <span class="mr-1"><?= h($persona['icon']) ?></span>
                        <?php endif; ?>
                        <span class="text-white font-medium text-sm"><?= h($persona['title'] ?? '') ?></span>
                    </div>
                    <?php if (!empty($persona['description'])): ?>
                        <div x-show="active === <?= $pIndex ?>" x-transition x-cloak
                             class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 rounded-lg p-3 text-xs text-gray-300 z-10"
                             style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1);">
                            <?= h($persona['description']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Mid-CTA #2 -->
<?php if (!empty($targetAudience)): ?>
<div class="dark-alt-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="py-16 lg:py-20 relative overflow-hidden" style="background: linear-gradient(135deg, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08), rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.03)), #0f172a;">
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3"><?= h($sh('mid_cta_2', 'Ready to Take the Next Step?')) ?></h2>
        <p class="text-gray-400 mb-8 max-w-lg mx-auto"><?= h($sh('mid_cta_2_sub', 'Join thousands of others who have already downloaded this guide.')) ?></p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-glow inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg text-base"
           style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- 8. Author Bio -->
<?php if (!empty($authorBio)): ?>
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-alt-bg py-20 lg:py-28">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl p-8" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
            <div class="flex items-start space-x-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.15);">
                    <svg class="w-7 h-7" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2"><?= h($sh('author_title', 'About the Author')) ?></h3>
                    <p class="text-gray-300 leading-relaxed"><?= h($authorBio) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 9. Testimonials — Chat bubble / message style -->
<?php if (!empty($testimonials)): ?>
<div class="<?= !empty($authorBio) ? 'dark-alt-bg' : 'dark-bg' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-bg py-20 lg:py-28">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-12"><?= h($sh('testimonials_title', 'What Readers Say')) ?></h2>
        <div class="space-y-6">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-1" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.2);">
                        <span class="font-bold text-sm" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);"><?= h(mb_substr($testimonial['name'] ?? '?', 0, 1)) ?></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-white"><?= h($testimonial['name'] ?? '') ?></span>
                            <?php if (!empty($testimonial['title'])): ?>
                                <span class="text-xs text-gray-500"><?= h($testimonial['title']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="chat-bubble p-4">
                            <p class="text-gray-300 text-sm leading-relaxed"><?= h($testimonial['quote'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 10. FAQ — Terminal-style with > prefix -->
<?php if (!empty($faqItems)): ?>
<div class="dark-bg"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="dark-alt-bg py-20 lg:py-28">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white text-center mb-12"><?= h($sh('faq_title', 'Frequently Asked Questions')) ?></h2>
        <div class="space-y-3" x-data="{ openFaq: null }">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="rounded-xl overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <button type="button" @click="openFaq = openFaq === <?= $index ?> ? null : <?= $index ?>"
                        class="w-full flex items-center justify-between px-5 py-4 text-left transition hover:bg-white/[0.02]">
                        <span class="flex items-center gap-2">
                            <span class="mono text-sm flex-shrink-0" style="color: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">&gt;</span>
                            <span class="font-medium text-white text-sm"><?= h($faq['question'] ?? '') ?></span>
                        </span>
                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 flex-shrink-0 ml-3" :class="openFaq === <?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === <?= $index ?>" x-transition x-cloak>
                        <div class="px-5 pb-4 pl-10 text-gray-400 text-sm leading-relaxed"><?= h($faq['answer'] ?? '') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 11. Bottom CTA — "Deploy" style -->
<div class="<?= !empty($faqItems) ? 'dark-alt-bg' : 'dark-bg' ?>"><div class="max-w-5xl mx-auto px-8"><div class="glow-divider"></div></div></div>
<section class="relative overflow-hidden py-20 lg:py-28" style="background: linear-gradient(135deg, #0f172a, rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.08), #0f172a);">
    <div class="absolute top-5 right-16 w-48 h-48 rounded-full pointer-events-none opacity-20 blur-3xl" style="background: rgba(<?= $r ?>,<?= $g ?>,<?= $b ?>,0.3); animation: floatOrb1 20s ease-in-out infinite;"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4"><?= h($sh('cta_title', 'Ready to Get Started?')) ?></h2>
        <p class="text-gray-400 mb-2 max-w-lg mx-auto"><?= h($sh('cta_subtitle', 'Download your free copy now and start implementing today.')) ?></p>
        <p class="mono text-xs text-gray-600 mb-8">$ download --format=pdf --free</p>
        <a href="#signup-form" onclick="document.getElementById('signup-form').scrollIntoView({behavior: 'smooth'}); return false;"
           class="btn-glow inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg text-base"
           style="background: rgb(<?= $r ?>,<?= $g ?>,<?= $b ?>);">
            <?= h($leadMagnet['hero_cta_text'] ?? 'Download Free') ?>
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
