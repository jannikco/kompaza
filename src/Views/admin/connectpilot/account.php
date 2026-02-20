<?php $pageTitle = 'LinkedIn Account'; $currentPage = 'connectpilot'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to ConnectPilot
    </a>
</div>

<?php if ($linkedinAccount && $linkedinAccount['status'] === 'active'): ?>
<!-- Connected Account Info -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-white">Connected Account</h3>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span>
            Active
        </span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="text-sm text-gray-400 mb-1">Name</p>
            <p class="text-white font-medium"><?= h($linkedinAccount['linkedin_name']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-400 mb-1">Email / Identifier</p>
            <p class="text-white font-medium"><?= h($linkedinAccount['linkedin_email'] ?: 'N/A') ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-400 mb-1">Profile URL</p>
            <?php if ($linkedinAccount['linkedin_profile_url']): ?>
            <a href="<?= h($linkedinAccount['linkedin_profile_url']) ?>" target="_blank" class="text-indigo-400 hover:text-indigo-300 text-sm">
                <?= h($linkedinAccount['linkedin_profile_url']) ?>
            </a>
            <?php else: ?>
            <p class="text-white">N/A</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-400">Daily Connection Limit</p>
            <p class="text-white font-medium"><?= (int)($linkedinAccount['daily_connection_limit'] ?? 20) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-400">Daily Message Limit</p>
            <p class="text-white font-medium"><?= (int)($linkedinAccount['daily_message_limit'] ?? 50) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Connection Form -->
<form method="POST" action="/admin/connectpilot/konto/gem" class="space-y-8">
    <?= csrfField() ?>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-2">
            <?= ($linkedinAccount && $linkedinAccount['status'] === 'active') ? 'Reconnect LinkedIn' : 'Connect LinkedIn Account' ?>
        </h3>
        <p class="text-sm text-gray-400 mb-6">Enter your LinkedIn session cookies to connect your account. ConnectPilot uses these to automate actions on your behalf.</p>

        <!-- Instructions -->
        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-yellow-400 mb-2">How to get your LinkedIn cookies</h4>
            <ol class="text-sm text-gray-300 space-y-2 list-decimal list-inside">
                <li>Open LinkedIn in your browser and make sure you are logged in.</li>
                <li>Open DevTools (F12 or Right-click &gt; Inspect).</li>
                <li>Go to the <strong class="text-white">Application</strong> tab (Chrome) or <strong class="text-white">Storage</strong> tab (Firefox).</li>
                <li>Under <strong class="text-white">Cookies</strong>, click on <code class="text-indigo-400">https://www.linkedin.com</code>.</li>
                <li>Find the cookie named <code class="text-indigo-400">li_at</code> and copy its value.</li>
                <li>Find the cookie named <code class="text-indigo-400">JSESSIONID</code> and copy its value (remove surrounding quotes if present).</li>
            </ol>
            <p class="text-xs text-gray-500 mt-3">Note: These cookies expire periodically. You may need to reconnect when they expire.</p>
        </div>

        <div class="space-y-6">
            <div>
                <label for="li_at_cookie" class="block text-sm font-medium text-gray-300 mb-2">li_at Cookie</label>
                <textarea name="li_at_cookie" id="li_at_cookie" rows="3" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder="Paste your li_at cookie value here..."><?= h($linkedinAccount['li_at_cookie'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="csrf_token" class="block text-sm font-medium text-gray-300 mb-2">JSESSIONID / CSRF Token</label>
                <input type="text" name="csrf_token" id="csrf_token" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder="ajax:1234567890123456789"
                    value="<?= h($linkedinAccount['csrf_token'] ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- Daily Limits -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Daily Limits</h3>
        <p class="text-sm text-gray-400 mb-6">Set daily action limits to stay within LinkedIn's usage guidelines and avoid account restrictions.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="daily_connection_limit" class="block text-sm font-medium text-gray-300 mb-2">Connection Requests per Day</label>
                <input type="number" name="daily_connection_limit" id="daily_connection_limit" min="1" max="100"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= (int)($linkedinAccount['daily_connection_limit'] ?? 20) ?>">
                <p class="text-xs text-gray-500 mt-1">Recommended: 15-25 per day</p>
            </div>
            <div>
                <label for="daily_message_limit" class="block text-sm font-medium text-gray-300 mb-2">Messages per Day</label>
                <input type="number" name="daily_message_limit" id="daily_message_limit" min="1" max="200"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= (int)($linkedinAccount['daily_message_limit'] ?? 50) ?>">
                <p class="text-xs text-gray-500 mt-1">Recommended: 30-50 per day</p>
            </div>
        </div>
    </div>

    <!-- Validate + Submit -->
    <div class="flex items-center justify-between">
        <button type="button" id="validateBtn"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition"
            onclick="validateCookie()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Test Connection
        </button>
        <div class="flex items-center space-x-4">
            <a href="/admin/connectpilot" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Save &amp; Connect
            </button>
        </div>
    </div>
</form>

<!-- Validation result area -->
<div id="validateResult" class="mt-4 hidden"></div>

<script>
async function validateCookie() {
    const btn = document.getElementById('validateBtn');
    const resultDiv = document.getElementById('validateResult');
    const liAt = document.getElementById('li_at_cookie').value.trim();
    const csrf = document.getElementById('csrf_token').value.trim();

    if (!liAt || !csrf) {
        resultDiv.className = 'mt-4 rounded-lg p-4 bg-red-900/50 border border-red-700 text-red-300';
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
            resultDiv.className = 'mt-4 rounded-lg p-4 bg-green-900/50 border border-green-700 text-green-300';
            resultDiv.innerHTML = '<strong>Connection successful!</strong> Found profile: ' + (data.profile.name || 'Unknown') + (data.profile.email ? ' (' + data.profile.email + ')' : '');
        } else {
            resultDiv.className = 'mt-4 rounded-lg p-4 bg-red-900/50 border border-red-700 text-red-300';
            resultDiv.textContent = data.error || 'Validation failed. Please check your cookies.';
        }
    } catch (err) {
        resultDiv.className = 'mt-4 rounded-lg p-4 bg-red-900/50 border border-red-700 text-red-300';
        resultDiv.textContent = 'Network error. Please try again.';
    }

    resultDiv.classList.remove('hidden');
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Test Connection';
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
