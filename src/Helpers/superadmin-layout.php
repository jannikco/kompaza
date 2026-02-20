<?php

function renderSuperadminPage($pageTitle, $currentPage, $viewFile, $data = []) {
    extract($data);

    ob_start();
    include VIEWS_PATH . '/' . $viewFile . '.php';
    $content = ob_get_clean();

    include VIEWS_PATH . '/superadmin/layouts/layout.php';
}
