<?php

$tenant = currentTenant();
view('admin/dashboard', ['tenant' => $tenant]);
