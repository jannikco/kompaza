<?php

use App\Models\Setting;

$settings = Setting::allGlobal();

view('superadmin/settings/index', ['settings' => $settings]);
