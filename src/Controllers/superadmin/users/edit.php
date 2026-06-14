<?php

// Show the edit form for any platform user.
use App\Models\User;
use App\Models\Tenant;

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    flashMessage('error', 'User not found.');
    redirect('/users');
}

$user = User::find($id);
if (!$user) {
    flashMessage('error', 'User not found.');
    redirect('/users');
}

$tenants = Tenant::all();

view('superadmin/users/edit', [
    'user' => $user,
    'tenants' => $tenants,
]);
