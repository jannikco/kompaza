<?php $pageTitle = 'LinkedIn Account'; $currentPage = 'connectpilot'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to ConnectPilot
    </a>
</div>

<?php if (!($encryptionReady ?? true)): ?>
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
    <div class="flex">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.48 0L3.16 16.25A2 2 0 005 19z"/></svg>
        <div class="ml-3">
            <p class="text-sm font-semibold text-red-800">LinkedIn automation is disabled</p>
            <p class="mt-1 text-sm text-red-700">
                The server encryption key is not configured, so LinkedIn sessions cannot be
                stored securely. Sessions are never saved in plain text, so connecting an
                account will fail until an administrator sets <code class="font-mono">OUTREACH_ENCRYPTION_KEY</code>.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($linkedinAccount && $linkedinAccount['status'] === 'active'): ?>
<!-- Connected Account Info -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Connected Account</h3>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span>
            Active
        </span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="text-sm text-gray-500 mb-1">Name</p>
            <p class="text-white font-medium"><?= h($linkedinAccount['linkedin_name']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500 mb-1">Email / Identifier</p>
            <p class="text-white font-medium"><?= h($linkedinAccount['linkedin_email'] ?: 'N/A') ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500 mb-1">Profile URL</p>
            <?php if ($linkedinAccount['linkedin_profile_url']): ?>
            <a href="<?= h($linkedinAccount['linkedin_profile_url']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-500 text-sm">
                <?= h($linkedinAccount['linkedin_profile_url']) ?>
            </a>
            <?php else: ?>
            <p class="text-white">N/A</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Daily Connection Limit</p>
            <p class="text-white font-medium"><?= (int)($linkedinAccount['daily_connection_limit'] ?? 20) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Daily Message Limit</p>
            <p class="text-white font-medium"><?= (int)($linkedinAccount['daily_message_limit'] ?? 50) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Connection Form -->
<form method="POST" action="/admin/connectpilot/konto/gem" class="space-y-8">
    <?= csrfField() ?>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            <?= ($linkedinAccount && $linkedinAccount['status'] === 'active') ? 'Reconnect LinkedIn' : 'Connect LinkedIn Account' ?>
        </h3>
        <p class="text-sm text-gray-500 mb-6">Enter your LinkedIn session cookies to connect your account. ConnectPilot uses these to automate actions on your behalf.</p>

        <!-- Instructions -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-yellow-600 mb-2">How to get your LinkedIn cookies</h4>
            <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                <li>Open LinkedIn in your browser and make sure you are logged in.</li>
                <li>Open DevTools (F12 or Right-click &gt; Inspect).</li>
                <li>Go to the <strong class="text-white">Application</strong> tab (Chrome) or <strong class="text-white">Storage</strong> tab (Firefox).</li>
                <li>Under <strong class="text-white">Cookies</strong>, click on <code class="text-indigo-600">https://www.linkedin.com</code>.</li>
                <li>Find the cookie named <code class="text-indigo-600">li_at</code> and copy its value.</li>
                <li>Find the cookie named <code class="text-indigo-600">JSESSIONID</code> and copy its value (remove surrounding quotes if present).</li>
            </ol>
            <p class="text-xs text-gray-500 mt-3">Note: These cookies expire periodically. You may need to reconnect when they expire.</p>
        </div>

        <div class="space-y-6">
            <div>
                <label for="li_at_cookie" class="block text-sm font-medium text-gray-700 mb-2">li_at Cookie</label>
                <?php // Never render the stored cookie back into the page. It is encrypted
                      // at rest precisely so it cannot be read again; echoing it here would
                      // hand it to anything that can see this HTML. ?>
                <textarea name="li_at_cookie" id="li_at_cookie" rows="3" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder="Paste your li_at cookie value here..."></textarea>
                <?php if (!empty($linkedinAccount['session_synced_at'])): ?>
                    <p class="mt-2 text-xs text-gray-500">
                        A session is already stored (last updated
                        <?= h(formatDate($linkedinAccount['session_synced_at'], 'd M Y H:i')) ?>).
                        Paste a new cookie only if you want to replace it.
                    </p>
                <?php endif; ?>
            </div>

            <div>
                <label for="csrf_token" class="block text-sm font-medium text-gray-700 mb-2">JSESSIONID / CSRF Token</label>
                <input type="text" name="csrf_token" id="csrf_token" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder="ajax:1234567890123456789"
                    value="">
            </div>
        </div>
    </div>

    <!-- Daily Limits -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Daily Limits</h3>
        <p class="text-sm text-gray-500 mb-6">Set daily action limits to stay within LinkedIn's usage guidelines and avoid account restrictions.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="daily_connection_limit" class="block text-sm font-medium text-gray-700 mb-2">Connection Requests per Day</label>
                <input type="number" name="daily_connection_limit" id="daily_connection_limit" min="0" max="100"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= (int)($linkedinAccount['daily_connection_limit'] ?? 20) ?>">
                <p class="text-xs text-gray-500 mt-1">Recommended: 15-25 per day. 0 pauses connection requests.</p>
            </div>
            <div>
                <label for="daily_message_limit" class="block text-sm font-medium text-gray-700 mb-2">Messages per Day</label>
                <input type="number" name="daily_message_limit" id="daily_message_limit" min="0" max="200"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= (int)($linkedinAccount['daily_message_limit'] ?? 50) ?>">
                <p class="text-xs text-gray-500 mt-1">Recommended: 30-50 per day. <strong>0 is an emergency stop</strong> — no DMs go out.</p>
            </div>
        </div>
        <label class="flex items-start gap-3 mt-6">
            <input type="checkbox" name="emergency_stop" id="emergency_stop" value="1" class="mt-1 rounded border-gray-300 text-red-600"
                <?= !empty($linkedinAccount['emergency_stop']) ? 'checked' : '' ?>>
            <span>
                <span class="block text-sm font-medium text-gray-900">Emergency stop</span>
                <span class="block text-xs text-gray-500">Pause every LinkedIn write (DMs, comments, reactions, invites) until you turn this off.</span>
            </span>
        </label>
    </div>

    <!-- Validate + Submit -->
    <div class="flex items-center justify-between">
        <button type="button" id="validateBtn"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
            onclick="validateCookie()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Test Connection
        </button>
        <div class="flex items-center space-x-4">
            <a href="/admin/connectpilot" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" id="saveConnectBtn" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Save &amp; Connect
            </button>
        </div>
    </div>
</form>

<!-- Validation result area -->
<div id="validateResult" class="mt-4 hidden"></div>

<script>
document.querySelector('form[action="/admin/connectpilot/konto/gem"]')?.addEventListener('submit', function (e) {
    const cap = parseInt(document.getElementById('daily_message_limit').value, 10);
    const stopped = document.getElementById('emergency_stop')?.checked;
    if ((cap === 0 || stopped) && !confirm('This will stop all LinkedIn sending (DMs, comments, invites) until you raise the cap or turn emergency stop off. Continue?')) {
        e.preventDefault();
    }
});

async function validateCookie() {
    const btn = document.getElementById('validateBtn');
    const resultDiv = document.getElementById('validateResult');
    const liAt = document.getElementById('li_at_cookie').value.trim();
    const csrf = document.getElementById('csrf_token').value.trim();

    if (!liAt || !csrf) {
        resultDiv.className = 'mt-4 rounded-lg p-4 bg-red-50 border border-red-200 text-red-800';
        resultDiv.textContent = 'Please enter both the li_at cookie and CSRF token.';
        resultDiv.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Validating...';

    try {
        const response = await fetch('/api/connectpilot/validate-cookie', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ li_at_cookie: liAt, csrf_token: csrf })
        });
        const data = await response.json();

        if (data.success) {
            resultDiv.className = 'mt-4 rounded-lg p-4 bg-green-50 border border-green-200 text-green-800';
            resultDiv.innerHTML = '<strong>Connection successful!</strong> Found profile: ' + (data.profile.name || 'Unknown') + (data.profile.email ? ' (' + data.profile.email + ')' : '');
        } else {
            resultDiv.className = 'mt-4 rounded-lg p-4 bg-red-50 border border-red-200 text-red-800';
            resultDiv.textContent = data.error || 'Validation failed. Please check your cookies.';
        }
    } catch (err) {
        resultDiv.className = 'mt-4 rounded-lg p-4 bg-red-50 border border-red-200 text-red-800';
        resultDiv.textContent = 'Network error. Please try again.';
    }

    resultDiv.classList.remove('hidden');
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Test Connection';
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
