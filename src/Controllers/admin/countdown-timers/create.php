<?php

use App\Auth\Auth;

Auth::requireTenantAdmin();

view('admin/countdown-timers/form', [
    'timer' => null,
]);
