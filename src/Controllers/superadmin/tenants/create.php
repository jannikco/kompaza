<?php

use App\Models\Plan;

$plans = Plan::all();

view('superadmin/tenants/create', [
    'plans' => $plans,
]);
