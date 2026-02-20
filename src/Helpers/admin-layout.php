<?php

function renderAdminPage($pageTitle, $currentPage, $viewFile, $data = []) {
    extract($data);

    $tenant = currentTenant();

    ob_start();
    include VIEWS_PATH . '/' . $viewFile . '.php';
    $content = ob_get_clean();

    include VIEWS_PATH . '/admin/layouts/admin-layout.php';
}
