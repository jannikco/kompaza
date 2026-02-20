<?php

view('admin/discount-codes/form', [
    'tenant' => currentTenant(),
    'discountCode' => null,
    'isEdit' => false,
]);
