<?php

view('admin/email-sequences/form', [
    'tenant' => currentTenant(),
    'sequence' => null,
    'steps' => [],
    'enrollments' => [],
]);
