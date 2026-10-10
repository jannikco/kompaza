<?php

use App\Models\LinkedInAccount;
use App\Services\Crypto\SecretBox;

$tenantId = currentTenantId();
$tenant = currentTenant();

// toPublicArray strips every cookie-bearing column, so nothing secret can reach
// the template even if a future edit renders the array wholesale.
$account = LinkedInAccount::forTenant($tenantId);
$linkedinAccount = $account ? LinkedInAccount::toPublicArray($account) : null;

view('admin/connectpilot/account', [
    'tenant' => $tenant,
    'linkedinAccount' => $linkedinAccount,
    'encryptionReady' => SecretBox::isConfigured(),
]);
