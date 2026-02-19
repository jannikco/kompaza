<?php

use App\Models\Plan;

$id = (int)($_GET['id'] ?? 0);
$plan = Plan::find($id);
if (!$plan) {
    flashMessage('error', 'Plan not found.');
    redirect('/plans');
}

view('superadmin/plans/edit', ['plan' => $plan]);
