<?php

use App\Models\Plan;

$plans = Plan::all();

view('superadmin/plans/index', [
    'plans' => $plans,
]);
