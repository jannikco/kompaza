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
            '/faq' => 'faq',
            '/about' => 'about',
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
            '/tenants/subscriptions' => 'subscriptions/index',
            '/tenants/revenue' => 'revenue/index',
            '/settings' => 'settings/index',
            '/logout' => 'logout',
        ],
        'POST' => [
            '/login' => 'login-submit',
            '/tenants/store' => 'tenants/store',
            '/tenants/update' => 'tenants/update',
            '/tenants/impersonate' => 'tenants/impersonate',
            '/plans/store' => 'plans/store',
            '/plans/update' => 'plans/update',
            '/plans/sync-stripe' => 'plans/sync-stripe',
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
            '/konto/kurser' => 'shop/account/courses',
            '/konto/certificates' => 'shop/account/certificates',
            '/courses' => 'shop/courses',
            '/forgot-password' => 'shop/forgot-password',
            '/reset-password' => 'shop/reset-password',
            '/contact' => 'shop/contact',
            '/terms' => 'shop/terms-of-service',
            '/certificate/download' => 'shop/certificate-download',
        ],
        'POST' => [
            '/login' => 'shop/login-submit',
            '/registrer' => 'shop/register-submit',
            '/lp/tilmeld' => 'shop/lead-magnet-signup',
            '/checkout/submit' => 'shop/checkout-submit',
            '/api/newsletter' => 'api/newsletter-signup',
            '/konto/indstillinger' => 'shop/account/settings-update',
            '/forgot-password' => 'shop/forgot-password-submit',
            '/reset-password' => 'shop/reset-password-submit',
            '/contact' => 'shop/contact-submit',
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
            '/admin/connectpilot' => 'admin/connectpilot/dashboard',
            '/admin/connectpilot/kampagner' => 'admin/connectpilot/campaigns/index',
            '/admin/connectpilot/kampagner/opret' => 'admin/connectpilot/campaigns/create',
            '/admin/connectpilot/kampagner/rediger' => 'admin/connectpilot/campaigns/edit',
            '/admin/connectpilot/leads' => 'admin/connectpilot/leads/index',
            '/admin/connectpilot/konto' => 'admin/connectpilot/account',
            '/admin/tilmeldinger' => 'admin/signups/index',
            '/admin/tilmeldinger/eksport' => 'admin/signups/export',
            '/admin/indstillinger' => 'admin/settings/index',
            '/admin/brugere' => 'admin/users/index',
            '/admin/brugere/opret' => 'admin/users/create',
            '/admin/brugere/rediger' => 'admin/users/edit',
            '/admin/kurser' => 'admin/courses/index',
            '/admin/kurser/opret' => 'admin/courses/create',
            '/admin/kurser/rediger' => 'admin/courses/edit',
            '/admin/kurser/lektion/opret' => 'admin/courses/lesson-create',
            '/admin/kurser/lektion' => 'admin/courses/lesson-edit',
            '/admin/kurser/tilmeldinger' => 'admin/courses/enrollments',
            '/admin/abonnement' => 'admin/subscription/index',
            '/admin/abonnement/succes' => 'admin/subscription/success',
            '/admin/abonnement/annuller' => 'admin/subscription/cancel',
            '/admin/abonnement/genoptag' => 'admin/subscription/resume',
            '/admin/abonnement/portal' => 'admin/subscription/portal',
            '/admin/stripe-connect' => 'admin/stripe-connect/index',
            '/admin/stripe-connect/forbind' => 'admin/stripe-connect/connect',
            '/admin/stripe-connect/callback' => 'admin/stripe-connect/callback',
            '/admin/stripe-connect/dashboard' => 'admin/stripe-connect/dashboard',
            '/admin/salg' => 'admin/sales/index',
            '/admin/kurser/quiz/opret' => 'admin/courses/quiz-create',
            '/admin/kurser/quiz/rediger' => 'admin/courses/quiz-edit',
            '/admin/certificates' => 'admin/courses/certificates',
            '/admin/contact-messages' => 'admin/contact-messages/index',
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
            '/admin/connectpilot/kampagner/gem' => 'admin/connectpilot/campaigns/store',
            '/admin/connectpilot/kampagner/opdater' => 'admin/connectpilot/campaigns/update',
            '/admin/connectpilot/kampagner/slet' => 'admin/connectpilot/campaigns/delete',
            '/admin/connectpilot/konto/gem' => 'admin/connectpilot/account-store',
            '/admin/tilmeldinger/slet' => 'admin/signups/delete',
            '/admin/indstillinger/opdater' => 'admin/settings/update',
            '/admin/brugere/gem' => 'admin/users/store',
            '/admin/brugere/opdater' => 'admin/users/update',
            '/admin/brugere/slet' => 'admin/users/delete',
            '/admin/kurser/gem' => 'admin/courses/store',
            '/admin/kurser/opdater' => 'admin/courses/update',
            '/admin/kurser/slet' => 'admin/courses/delete',
            '/admin/kurser/modul/gem' => 'admin/courses/module-store',
            '/admin/kurser/modul/opdater' => 'admin/courses/module-update',
            '/admin/kurser/modul/slet' => 'admin/courses/module-delete',
            '/admin/kurser/modul/sorter' => 'admin/courses/module-reorder',
            '/admin/kurser/lektion/gem' => 'admin/courses/lesson-store',
            '/admin/kurser/lektion/opdater' => 'admin/courses/lesson-update',
            '/admin/kurser/lektion/slet' => 'admin/courses/lesson-delete',
            '/admin/kurser/lektion/sorter' => 'admin/courses/lesson-reorder',
            '/admin/kurser/tilmeld' => 'admin/courses/enroll',
            '/admin/kurser/afmeld' => 'admin/courses/unenroll',
            '/admin/abonnement/checkout' => 'admin/subscription/checkout',
            '/admin/kurser/quiz/gem' => 'admin/courses/quiz-store',
            '/admin/kurser/quiz/opdater' => 'admin/courses/quiz-update',
            '/admin/kurser/quiz/slet' => 'admin/courses/quiz-delete',
            '/admin/kurser/quiz/spoergsmaal/gem' => 'admin/courses/question-store',
            '/admin/kurser/quiz/spoergsmaal/opdater' => 'admin/courses/question-update',
            '/admin/kurser/quiz/spoergsmaal/slet' => 'admin/courses/question-delete',
            '/admin/certificates/revoke' => 'admin/courses/certificate-revoke',
            '/admin/kurser/attachment/gem' => 'admin/courses/attachment-store',
            '/admin/kurser/attachment/slet' => 'admin/courses/attachment-delete',
            '/admin/contact-messages/reply' => 'admin/contact-messages/reply',
            '/admin/contact-messages/slet' => 'admin/contact-messages/delete',
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
        // Course: detail page /course/{slug}
        elseif ($method === 'GET' && preg_match('#^/course/([a-z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/course';
            $dynamicParams['slug'] = $matches[1];
        }
        // Course: player /course/{slug}/learn
        elseif ($method === 'GET' && preg_match('#^/course/([a-z0-9\-]+)/learn$#', $request, $matches)) {
            $controller = 'shop/course-player';
            $dynamicParams['slug'] = $matches[1];
        }
        // Course: player with lesson /course/{slug}/learn/{lesson_id}
        elseif ($method === 'GET' && preg_match('#^/course/([a-z0-9\-]+)/learn/(\d+)$#', $request, $matches)) {
            $controller = 'shop/course-player';
            $dynamicParams['slug'] = $matches[1];
            $dynamicParams['lesson_id'] = $matches[2];
        }
        // Course: buy
        elseif ($method === 'POST' && preg_match('#^/course/([a-z0-9\-]+)/buy$#', $request, $matches)) {
            $controller = 'shop/course-buy';
            $dynamicParams['slug'] = $matches[1];
        }
        // Course: subscribe
        elseif ($method === 'POST' && preg_match('#^/course/([a-z0-9\-]+)/subscribe$#', $request, $matches)) {
            $controller = 'shop/course-subscribe';
            $dynamicParams['slug'] = $matches[1];
        }
        // Course: enroll free
        elseif ($method === 'POST' && preg_match('#^/course/([a-z0-9\-]+)/enroll-free$#', $request, $matches)) {
            $controller = 'shop/course-enroll-free';
            $dynamicParams['slug'] = $matches[1];
        }
        // Quiz: take quiz
        elseif ($method === 'GET' && $request === '/course/quiz') {
            $controller = 'shop/course-quiz';
        }
        // Quiz: submit answers
        elseif ($method === 'POST' && $request === '/course/quiz/submit') {
            $controller = 'shop/course-quiz-submit';
        }
        // Certificate: generate
        elseif ($method === 'GET' && preg_match('#^/course/([a-z0-9\-]+)/certificate$#', $request, $matches)) {
            $controller = 'shop/certificate-generate';
            $dynamicParams['slug'] = $matches[1];
        }
        // Certificate: public verify
        elseif ($method === 'GET' && preg_match('#^/certificate/verify/([A-Z0-9\-]+)$#', $request, $matches)) {
            $controller = 'shop/certificate-verify';
            $dynamicParams['slug'] = $matches[1];
        }
        // Lesson attachment download
        elseif ($method === 'GET' && $request === '/lesson/attachment/download') {
            $controller = 'shop/lesson-attachment-download';
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
        // ConnectPilot API endpoints
        elseif ($method === 'POST' && $request === '/api/connectpilot/validate-cookie') {
            Auth::requireTenantAdmin();
            $controller = 'api/connectpilot/validate-cookie';
        }
        // Course API endpoints
        elseif ($method === 'POST' && $request === '/api/courses/upload-chunk') {
            Auth::requireTenantAdmin();
            $controller = 'api/courses/upload-chunk';
        }
        elseif ($method === 'POST' && $request === '/api/courses/finalize-upload') {
            Auth::requireTenantAdmin();
            $controller = 'api/courses/finalize-upload';
        }
        elseif ($method === 'GET' && $request === '/api/courses/video-status') {
            Auth::requireTenantAdmin();
            $controller = 'api/courses/video-status';
        }
        elseif ($method === 'POST' && $request === '/api/courses/reorder-modules') {
            Auth::requireTenantAdmin();
            $controller = 'api/courses/reorder-modules';
        }
        elseif ($method === 'POST' && $request === '/api/courses/reorder-lessons') {
            Auth::requireTenantAdmin();
            $controller = 'api/courses/reorder-lessons';
        }
        elseif ($method === 'POST' && $request === '/api/courses/mark-complete') {
            $controller = 'api/courses/mark-complete';
        }
        elseif ($method === 'POST' && $request === '/api/courses/save-position') {
            $controller = 'api/courses/save-position';
        }
        elseif ($method === 'GET' && $request === '/api/courses/video-url') {
            $controller = 'api/courses/video-url';
        }
        // Stripe webhook
        elseif ($method === 'POST' && $request === '/api/webhooks/stripe') {
            $controller = 'api/webhooks/stripe';
        }
        // Stripe Connect webhook
        elseif ($method === 'POST' && $request === '/api/stripe/connect-webhook') {
            $controller = 'api/stripe-connect-webhook';
        }
        // Ebook checkout API
        elseif ($method === 'POST' && $request === '/api/ebook-checkout') {
            $controller = 'api/ebook-checkout';
        }
        // Ebook purchase success: /ebog/kob-succes/{session_id}
        elseif ($method === 'GET' && preg_match('#^/ebog/kob-succes/([a-zA-Z0-9_]+)$#', $request, $matches)) {
            $controller = 'ebook-purchase-success';
            $dynamicParams['session_id'] = $matches[1];
        }
        // Ebook download with token
        elseif ($method === 'GET' && preg_match('#^/ebog/download/([a-zA-Z0-9]+)$#', $request, $matches)) {
            $controller = 'ebook-download';
            $dynamicParams['token'] = $matches[1];
        }
        // Impersonate login (HMAC-signed, no auth required)
        elseif ($method === 'GET' && $request === '/auth/impersonate') {
            $controller = 'shop/impersonate';
        }
    }

    // Load controller
    if ($controller) {
        $controllerPath = CONTROLLERS_PATH . '/' . $controller . '.php';
        if (file_exists($controllerPath)) {
            $slug = $dynamicParams['slug'] ?? null;
            $token = $dynamicParams['token'] ?? null;
            $id = $dynamicParams['id'] ?? null;
            $lesson_id = $dynamicParams['lesson_id'] ?? null;
            $session_id = $dynamicParams['session_id'] ?? null;
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
