<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

require_once __DIR__ . '/../src/Config/config.php';

use App\Auth\Auth;
use App\Services\TenantResolver;

// Start session for cart etc.
session_start();

// Load auth from cookie
Auth::loadFromCookie();

// Resolve tenant and routing mode
$routingMode = TenantResolver::resolve();
$tenant = TenantResolver::current();

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($request !== '/' && substr($request, -1) === '/') {
    $request = rtrim($request, '/');
}
$method = $_SERVER['REQUEST_METHOD'];

$controller = null;
$dynamicParams = [];

// ============================================
// MARKETING ROUTES (kompaza.com)
// ============================================
if ($routingMode === 'marketing') {
    $routes = [
        'GET' => [
            '/' => 'home',
            '/pricing' => 'pricing',
            '/register' => 'register',
            '/login' => 'login',
        ],
        'POST' => [
            '/register' => 'register-submit',
            '/login' => 'login-submit',
        ],
    ];

    if (isset($routes[$method][$request])) {
        $controller = 'marketing/' . $routes[$method][$request];
    }

    // Load controller
    if ($controller) {
        $controllerPath = CONTROLLERS_PATH . '/' . $controller . '.php';
        if (file_exists($controllerPath)) {
            require $controllerPath;
        } else {
            http_response_code(404);
            view('errors/404');
        }
    } else {
        http_response_code(404);
        view('errors/404');
    }
    exit;
}

// ============================================
// SUPERADMIN ROUTES (superadmin.kompaza.com)
// ============================================
if ($routingMode === 'superadmin') {
    // All superadmin routes require auth
    if ($request !== '/login' && !($method === 'POST' && $request === '/login')) {
        Auth::requireSuperAdmin();
    }

    $routes = [
        'GET' => [
            '/' => 'dashboard',
            '/login' => 'login',
            '/tenants' => 'tenants/index',
            '/tenants/create' => 'tenants/create',
            '/tenants/edit' => 'tenants/edit',
            '/tenants/show' => 'tenants/show',
            '/plans' => 'plans/index',
            '/plans/create' => 'plans/create',
            '/plans/edit' => 'plans/edit',
            '/settings' => 'settings/index',
            '/logout' => 'logout',
        ],
        'POST' => [
            '/login' => 'login-submit',
            '/tenants/store' => 'tenants/store',
            '/tenants/update' => 'tenants/update',
            '/plans/store' => 'plans/store',
            '/plans/update' => 'plans/update',
            '/settings/update' => 'settings/update',
        ],
    ];

    if (isset($routes[$method][$request])) {
        $controller = 'superadmin/' . $routes[$method][$request];
    }

    if ($controller) {
        $controllerPath = CONTROLLERS_PATH . '/' . $controller . '.php';
        if (file_exists($controllerPath)) {
            require $controllerPath;
        } else {
            http_response_code(404);
            view('errors/404');
        }
    } else {
        http_response_code(404);
        view('errors/404');
    }
    exit;
}

// ============================================
// TENANT ROUTES ({slug}.kompaza.com or custom domain)
// ============================================
if ($routingMode === 'tenant') {
    // Ensure tenant is valid
    $tenant = TenantResolver::requireTenant();

    // --- Static routes ---
    $publicRoutes = [
        'GET' => [
            '/' => 'shop/home',
            '/blog' => 'shop/blog',
            '/eboger' => 'shop/ebooks',
            '/produkter' => 'shop/products',
            '/kurv' => 'shop/cart',
            '/checkout' => 'shop/checkout',
            '/privatlivspolitik' => 'shop/privacy-policy',
            '/login' => 'shop/login',
            '/registrer' => 'shop/register',
            '/logout' => 'shop/logout',
            '/konto' => 'shop/account/index',
            '/konto/ordrer' => 'shop/account/orders',
            '/konto/downloads' => 'shop/account/downloads',
            '/konto/indstillinger' => 'shop/account/settings',
        ],
        'POST' => [
            '/login' => 'shop/login-submit',
            '/registrer' => 'shop/register-submit',
            '/lp/tilmeld' => 'shop/lead-magnet-signup',
            '/checkout/submit' => 'shop/checkout-submit',
            '/api/newsletter' => 'api/newsletter-signup',
            '/konto/indstillinger' => 'shop/account/settings-update',
        ],
    ];

    $adminRoutes = [
        'GET' => [
            '/admin' => 'admin/dashboard',
            '/admin/lead-magnets' => 'admin/lead-magnets/index',
            '/admin/lead-magnets/opret' => 'admin/lead-magnets/create',
            '/admin/lead-magnets/rediger' => 'admin/lead-magnets/edit',
            '/admin/artikler' => 'admin/articles/index',
            '/admin/artikler/opret' => 'admin/articles/create',
            '/admin/artikler/rediger' => 'admin/articles/edit',
            '/admin/eboger' => 'admin/ebooks/index',
            '/admin/eboger/opret' => 'admin/ebooks/create',
            '/admin/eboger/rediger' => 'admin/ebooks/edit',
            '/admin/kunder' => 'admin/customers/index',
            '/admin/kunder/vis' => 'admin/customers/show',
            '/admin/kunder/opret' => 'admin/customers/create',
            '/admin/kunder/rediger' => 'admin/customers/edit',
            '/admin/kunder/eksport' => 'admin/customers/export',
            '/admin/ordrer' => 'admin/orders/index',
            '/admin/ordrer/vis' => 'admin/orders/show',
            '/admin/produkter' => 'admin/products/index',
            '/admin/produkter/opret' => 'admin/products/create',
            '/admin/produkter/rediger' => 'admin/products/edit',
            '/admin/leadshark' => 'admin/leadshark/dashboard',
            '/admin/leadshark/kampagner' => 'admin/leadshark/campaigns/index',
            '/admin/leadshark/kampagner/opret' => 'admin/leadshark/campaigns/create',
            '/admin/leadshark/kampagner/rediger' => 'admin/leadshark/campaigns/edit',
            '/admin/leadshark/leads' => 'admin/leadshark/leads/index',
            '/admin/leadshark/konto' => 'admin/leadshark/account',
            '/admin/tilmeldinger' => 'admin/signups/index',
            '/admin/tilmeldinger/eksport' => 'admin/signups/export',
            '/admin/indstillinger' => 'admin/settings/index',
            '/admin/brugere' => 'admin/users/index',
            '/admin/brugere/opret' => 'admin/users/create',
            '/admin/brugere/rediger' => 'admin/users/edit',
        ],
        'POST' => [
            '/admin/lead-magnets/gem' => 'admin/lead-magnets/store',
            '/admin/lead-magnets/opdater' => 'admin/lead-magnets/update',
            '/admin/lead-magnets/slet' => 'admin/lead-magnets/delete',
            '/admin/artikler/gem' => 'admin/articles/store',
            '/admin/artikler/opdater' => 'admin/articles/update',
            '/admin/artikler/slet' => 'admin/articles/delete',
            '/admin/eboger/gem' => 'admin/ebooks/store',
            '/admin/eboger/opdater' => 'admin/ebooks/update',
            '/admin/eboger/slet' => 'admin/ebooks/delete',
            '/admin/kunder/gem' => 'admin/customers/store',
            '/admin/kunder/opdater' => 'admin/customers/update',
            '/admin/kunder/slet' => 'admin/customers/delete',
            '/admin/ordrer/status' => 'admin/orders/update-status',
            '/admin/produkter/gem' => 'admin/products/store',
            '/admin/produkter/opdater' => 'admin/products/update',
            '/admin/produkter/slet' => 'admin/products/delete',
            '/admin/leadshark/kampagner/gem' => 'admin/leadshark/campaigns/store',
            '/admin/leadshark/kampagner/opdater' => 'admin/leadshark/campaigns/update',
            '/admin/leadshark/kampagner/slet' => 'admin/leadshark/campaigns/delete',
            '/admin/leadshark/konto/gem' => 'admin/leadshark/account-store',
            '/admin/tilmeldinger/slet' => 'admin/signups/delete',
            '/admin/indstillinger/opdater' => 'admin/settings/update',
            '/admin/brugere/gem' => 'admin/users/store',
            '/admin/brugere/opdater' => 'admin/users/update',
            '/admin/brugere/slet' => 'admin/users/delete',
        ],
    ];

    // Check static public routes
    if (isset($publicRoutes[$method][$request])) {
        $controller = $publicRoutes[$method][$request];
    }

    // Check static admin routes
    if (!$controller && isset($adminRoutes[$method][$request])) {
        $controller = $adminRoutes[$method][$request];
        // All admin routes require tenant_admin auth
        Auth::requireTenantAdmin();
    }

    // Dynamic routes
    if (!$controller) {
        // Blog article: /blog/{slug}
        if ($method === 'GET' && preg_match('#^/blog/([a-z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/article';
            $dynamicParams['slug'] = $matches[1];
        }
        // Ebook single: /ebog/{slug}
        elseif ($method === 'GET' && preg_match('#^/ebog/([a-z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/ebook';
            $dynamicParams['slug'] = $matches[1];
        }
        // Lead magnet: /lp/{slug}
        elseif ($method === 'GET' && preg_match('#^/lp/([a-z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/lead-magnet';
            $dynamicParams['slug'] = $matches[1];
        }
        // Lead magnet success: /lp/succes/{slug}
        elseif ($method === 'GET' && preg_match('#^/lp/succes/([a-z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/lead-magnet-success';
            $dynamicParams['slug'] = $matches[1];
        }
        // Lead magnet download: /lp/download/{token}
        elseif ($method === 'GET' && preg_match('#^/lp/download/([a-zA-Z0-9]+)$#', $request, $matches)) {
            $controller = 'shop/lead-magnet-download';
            $dynamicParams['token'] = $matches[1];
        }
        // Product single: /produkt/{slug}
        elseif ($method === 'GET' && preg_match('#^/produkt/([a-z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/product';
            $dynamicParams['slug'] = $matches[1];
        }
        // Customer order detail: /konto/ordrer/{id}
        elseif ($method === 'GET' && preg_match('#^/konto/ordrer/(\d+)$#', $request, $matches)) {
            $controller = 'shop/account/order-detail';
            $dynamicParams['id'] = $matches[1];
        }
        // Cart actions (AJAX)
        elseif ($method === 'POST' && $request === '/api/cart/add') {
            $controller = 'api/cart-add';
        }
        elseif ($method === 'POST' && $request === '/api/cart/update') {
            $controller = 'api/cart-update';
        }
        elseif ($method === 'POST' && $request === '/api/cart/remove') {
            $controller = 'api/cart-remove';
        }
        // LeadShark API endpoints
        elseif ($method === 'POST' && $request === '/api/leadshark/validate-cookie') {
            Auth::requireTenantAdmin();
            $controller = 'api/leadshark/validate-cookie';
        }
        // Stripe webhook
        elseif ($method === 'POST' && $request === '/api/webhooks/stripe') {
            $controller = 'api/webhooks/stripe';
        }
    }

    // Load controller
    if ($controller) {
        $controllerPath = CONTROLLERS_PATH . '/' . $controller . '.php';
        if (file_exists($controllerPath)) {
            $slug = $dynamicParams['slug'] ?? null;
            $token = $dynamicParams['token'] ?? null;
            $id = $dynamicParams['id'] ?? null;
            require $controllerPath;
        } else {
            http_response_code(404);
            view('errors/404');
        }
    } else {
        http_response_code(404);
        view('errors/404');
    }
    exit;
}

// Fallback
http_response_code(404);
echo 'Page not found';
