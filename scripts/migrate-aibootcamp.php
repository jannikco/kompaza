<?php
/**
 * AI BootCamp → Kompaza Migration Script
 *
 * Migrates data from the aibootcamp database to the kompaza database
 * for the aibootcamphq tenant.
 *
 * Run on app1 where both databases are accessible:
 *   php scripts/migrate-aibootcamp.php
 *   php scripts/migrate-aibootcamp.php --dry-run
 */

// ============================================
// Configuration
// ============================================

$dryRun = in_array('--dry-run', $argv ?? []);

// Database connections - adjust credentials as needed
$sourceDb = new PDO(
    'mysql:host=localhost;dbname=aibootcamp;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$targetDb = new PDO(
    'mysql:host=localhost;dbname=kompaza;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

// ============================================
// Resolve tenant
// ============================================

$tenant = $targetDb->query("SELECT * FROM tenants WHERE slug = 'aibootcamphq'")->fetch();
if (!$tenant) {
    echo "ERROR: Tenant 'aibootcamphq' not found. Run migration 012 first.\n";
    exit(1);
}
$tenantId = $tenant['id'];
echo "=== AI BootCamp → Kompaza Migration ===\n";
echo "Tenant ID: {$tenantId} ({$tenant['name']})\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no changes)" : "LIVE") . "\n\n";

// ID mapping arrays
$userMap = [];      // old user_id => new user_id
$courseMap = [];     // old course_id => new course_id
$moduleMap = [];    // old section_id => new module_id (aibootcamp sections → kompaza modules)
$lessonMap = [];    // old module_id => new lesson_id (aibootcamp modules → kompaza lessons)
$quizMap = [];      // old quiz_id => new quiz_id
$questionMap = [];  // old question_id => new question_id
$orderMap = [];     // old order_id => new order_id

$stats = [];

// ============================================
// Helper functions
// ============================================

function info($msg) {
    echo "  → {$msg}\n";
}

function progress($type, $count) {
    global $stats;
    $stats[$type] = ($stats[$type] ?? 0) + $count;
}

function generateOrderNumber() {
    return 'AB-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

// ============================================
// 1. Migrate Users (role='user' → role='customer')
// ============================================

echo "1. Migrating users...\n";
$users = $sourceDb->query("SELECT * FROM users WHERE role = 'user'")->fetchAll();
foreach ($users as $user) {
    // Check if email already exists for this tenant
    $existing = $targetDb->prepare("SELECT id FROM users WHERE email = ? AND tenant_id = ?");
    $existing->execute([$user['email'], $tenantId]);
    if ($existing->fetch()) {
        info("Skipping duplicate user: {$user['email']}");
        // Map to existing user
        $existingUser = $targetDb->prepare("SELECT id FROM users WHERE email = ? AND tenant_id = ?");
        $existingUser->execute([$user['email'], $tenantId]);
        $userMap[$user['id']] = $existingUser->fetch()['id'];
        continue;
    }

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO users (tenant_id, role, name, email, password_hash, phone, status, created_at, updated_at)
            VALUES (?, 'customer', ?, ?, ?, NULL, 'active', ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $user['name'],
            $user['email'],
            $user['password_hash'],
            $user['created_at'],
            $user['updated_at'],
        ]);
        $userMap[$user['id']] = $targetDb->lastInsertId();
    } else {
        $userMap[$user['id']] = 'dry-' . $user['id'];
    }
    progress('users', 1);
}
info("Migrated " . ($stats['users'] ?? 0) . " users");

// ============================================
// 2. Migrate Courses
// ============================================

echo "\n2. Migrating courses...\n";
$courses = $sourceDb->query("SELECT * FROM courses")->fetchAll();
foreach ($courses as $course) {
    // Determine pricing type
    $price = $course['price'] ?? 0;
    $pricingType = $price > 0 ? 'one_time' : 'free';

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO courses (tenant_id, slug, title, subtitle, description, short_description,
                pricing_type, price_dkk, status, instructor_name, instructor_bio,
                enrollment_count, total_duration_seconds, created_at, updated_at)
            VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $course['slug'],
            $course['title'],
            $course['description'] ?? null,
            $course['intro_text'] ?? null,
            $pricingType,
            $price > 0 ? $price : null,
            ($course['status'] ?? 'active') === 'active' ? 'published' : 'draft',
            $course['instructor_name'] ?? null,
            $course['instructor_bio'] ?? null,
            $course['enrollment_count'] ?? 0,
            $course['created_at'],
            $course['updated_at'],
        ]);
        $courseMap[$course['id']] = $targetDb->lastInsertId();
    } else {
        $courseMap[$course['id']] = 'dry-' . $course['id'];
    }
    info("Course: {$course['title']} (#{$course['id']} → " . ($courseMap[$course['id']] ?? '?') . ")");
    progress('courses', 1);
}

// ============================================
// 3. Migrate Sections → Course Modules
// ============================================

echo "\n3. Migrating sections → course_modules...\n";
$sections = $sourceDb->query("SELECT * FROM sections ORDER BY course_id, position")->fetchAll();
foreach ($sections as $section) {
    $newCourseId = $courseMap[$section['course_id']] ?? null;
    if (!$newCourseId) {
        info("Skipping section #{$section['id']}: no mapped course");
        continue;
    }

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO course_modules (course_id, tenant_id, title, description, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $newCourseId,
            $tenantId,
            $section['title'],
            $section['description'] ?? null,
            $section['position'],
            $section['created_at'],
            $section['updated_at'],
        ]);
        $moduleMap[$section['id']] = $targetDb->lastInsertId();
    } else {
        $moduleMap[$section['id']] = 'dry-' . $section['id'];
    }
    progress('modules', 1);
}
info("Migrated " . ($stats['modules'] ?? 0) . " sections → modules");

// ============================================
// 4. Migrate Modules → Course Lessons (video lessons)
// ============================================

echo "\n4. Migrating modules → course_lessons...\n";
$modules = $sourceDb->query("SELECT * FROM modules ORDER BY course_id, position")->fetchAll();
foreach ($modules as $module) {
    $newCourseId = $courseMap[$module['course_id']] ?? null;
    $newModuleId = $moduleMap[$module['section_id'] ?? 0] ?? null;

    if (!$newCourseId) {
        info("Skipping module #{$module['id']}: no mapped course");
        continue;
    }

    // If no section mapping, find the first module for this course
    if (!$newModuleId && !$dryRun) {
        $firstModule = $targetDb->prepare("SELECT id FROM course_modules WHERE course_id = ? ORDER BY sort_order LIMIT 1");
        $firstModule->execute([$newCourseId]);
        $fm = $firstModule->fetch();
        $newModuleId = $fm ? $fm['id'] : null;
    }

    if (!$newModuleId && !$dryRun) {
        // Create a default module
        $stmt = $targetDb->prepare("
            INSERT INTO course_modules (course_id, tenant_id, title, sort_order)
            VALUES (?, ?, 'General', 0)
        ");
        $stmt->execute([$newCourseId, $tenantId]);
        $newModuleId = $targetDb->lastInsertId();
    }

    $lessonType = !empty($module['s3_object_key']) ? 'video' : 'text';

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO course_lessons (module_id, course_id, tenant_id, title, slug,
                lesson_type, video_s3_key, video_duration_seconds,
                video_status, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '-', strtolower($module['title'])));
        $slug = preg_replace('/-+/', '-', trim($slug, '-'));
        $stmt->execute([
            $newModuleId,
            $newCourseId,
            $tenantId,
            $module['title'],
            $slug,
            $lessonType,
            $module['s3_object_key'] ?? null,
            $module['duration_seconds'] ?? null,
            !empty($module['s3_object_key']) ? 'ready' : null,
            $module['position'],
            $module['created_at'],
            $module['updated_at'],
        ]);
        $lessonMap[$module['id']] = $targetDb->lastInsertId();
    } else {
        $lessonMap[$module['id']] = 'dry-' . $module['id'];
    }
    progress('lessons', 1);
}
info("Migrated " . ($stats['lessons'] ?? 0) . " modules → lessons");

// ============================================
// 5. Migrate Module Attachments → Lesson Attachments
// ============================================

echo "\n5. Migrating module_attachments → lesson_attachments...\n";
$attachments = $sourceDb->query("SELECT * FROM module_attachments")->fetchAll();
foreach ($attachments as $att) {
    $newLessonId = $lessonMap[$att['module_id']] ?? null;
    if (!$newLessonId) {
        info("Skipping attachment #{$att['id']}: no mapped lesson");
        continue;
    }

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO lesson_attachments (tenant_id, lesson_id, title, file_path, file_type, file_size, download_count, sort_order, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $newLessonId,
            $att['title'],
            $att['file_path'],
            $att['file_type'] ?? null,
            $att['file_size'] ?? 0,
            $att['download_count'] ?? 0,
            $att['order_index'] ?? 0,
            $att['created_at'],
        ]);
    }
    progress('attachments', 1);
}
info("Migrated " . ($stats['attachments'] ?? 0) . " attachments");

// ============================================
// 6. Migrate Quizzes
// ============================================

echo "\n6. Migrating quizzes...\n";
$quizzes = $sourceDb->query("SELECT * FROM quizzes")->fetchAll();
foreach ($quizzes as $quiz) {
    $newCourseId = $courseMap[$quiz['course_id']] ?? null;
    $newModuleId = $quiz['module_id'] ? ($moduleMap[$quiz['module_id']] ?? null) : null;

    // For aibootcamp, module_id in quizzes refers to the modules table (=lessons)
    // We need the parent section/module. Let's find it via the lesson's module_id.
    $kompazaModuleId = null;
    if ($quiz['module_id'] && isset($lessonMap[$quiz['module_id']])) {
        $newLessonId = $lessonMap[$quiz['module_id']];
        if (!$dryRun) {
            $lessonRow = $targetDb->prepare("SELECT module_id FROM course_lessons WHERE id = ?");
            $lessonRow->execute([$newLessonId]);
            $lr = $lessonRow->fetch();
            $kompazaModuleId = $lr ? $lr['module_id'] : null;
        }
    }

    if (!$newCourseId) {
        info("Skipping quiz #{$quiz['id']}: no mapped course");
        continue;
    }

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO quizzes (tenant_id, course_id, module_id, title, description,
                pass_threshold, shuffle_questions, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, NULL, 80.00, ?, 'published', ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $newCourseId,
            $kompazaModuleId,
            $quiz['title'],
            $quiz['shuffle_questions'] ?? false,
            $quiz['created_at'],
            $quiz['updated_at'],
        ]);
        $quizMap[$quiz['id']] = $targetDb->lastInsertId();
    } else {
        $quizMap[$quiz['id']] = 'dry-' . $quiz['id'];
    }
    progress('quizzes', 1);
}
info("Migrated " . ($stats['quizzes'] ?? 0) . " quizzes");

// ============================================
// 7. Migrate Questions
// ============================================

echo "\n7. Migrating questions...\n";
$questions = $sourceDb->query("SELECT * FROM questions ORDER BY quiz_id, position")->fetchAll();
foreach ($questions as $q) {
    $newQuizId = $quizMap[$q['quiz_id']] ?? null;
    if (!$newQuizId) continue;

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO quiz_questions (quiz_id, tenant_id, text, position, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $newQuizId,
            $tenantId,
            $q['text'],
            $q['position'],
            $q['created_at'],
        ]);
        $questionMap[$q['id']] = $targetDb->lastInsertId();
    } else {
        $questionMap[$q['id']] = 'dry-' . $q['id'];
    }
    progress('questions', 1);
}
info("Migrated " . ($stats['questions'] ?? 0) . " questions");

// ============================================
// 8. Migrate Choices
// ============================================

echo "\n8. Migrating choices...\n";
$choices = $sourceDb->query("SELECT * FROM choices ORDER BY question_id, position")->fetchAll();
foreach ($choices as $c) {
    $newQuestionId = $questionMap[$c['question_id']] ?? null;
    if (!$newQuestionId) continue;

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO quiz_choices (question_id, text, is_correct, position)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $newQuestionId,
            $c['text'],
            $c['is_correct'],
            $c['position'],
        ]);
    }
    progress('choices', 1);
}
info("Migrated " . ($stats['choices'] ?? 0) . " choices");

// ============================================
// 9. Migrate Enrollments
// ============================================

echo "\n9. Migrating enrollments...\n";
$enrollments = $sourceDb->query("SELECT * FROM enrollments")->fetchAll();
foreach ($enrollments as $e) {
    $newUserId = $userMap[$e['user_id']] ?? null;
    $newCourseId = $courseMap[$e['course_id']] ?? null;
    if (!$newUserId || !$newCourseId) continue;

    if (!$dryRun) {
        // Check for duplicate
        $dup = $targetDb->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?");
        $dup->execute([$newCourseId, $newUserId]);
        if ($dup->fetch()) continue;

        $stmt = $targetDb->prepare("
            INSERT INTO course_enrollments (tenant_id, course_id, user_id, enrollment_source, status,
                enrolled_at, completed_at)
            VALUES (?, ?, ?, 'purchase', 'active', ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $newCourseId,
            $newUserId,
            $e['enrolled_at'],
            $e['completed_at'] ?? null,
        ]);
    }
    progress('enrollments', 1);
}
info("Migrated " . ($stats['enrollments'] ?? 0) . " enrollments");

// ============================================
// 10. Migrate Quiz Attempts
// ============================================

echo "\n10. Migrating quiz_attempts...\n";
$attempts = $sourceDb->query("SELECT * FROM quiz_attempts")->fetchAll();
foreach ($attempts as $a) {
    $newUserId = $userMap[$a['user_id']] ?? null;
    $newQuizId = $quizMap[$a['quiz_id']] ?? null;
    if (!$newUserId || !$newQuizId) continue;

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO quiz_attempts (tenant_id, user_id, quiz_id, score_percentage, passed,
                answers, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $newUserId,
            $newQuizId,
            $a['score_percentage'],
            $a['score_percentage'] >= 80 ? 1 : 0,
            $a['answers_payload'] ?? null,
            $a['ip_address'] ?? null,
            $a['attempted_at'],
        ]);
    }
    progress('quiz_attempts', 1);
}
info("Migrated " . ($stats['quiz_attempts'] ?? 0) . " quiz attempts");

// ============================================
// 11. Migrate Certificates
// ============================================

echo "\n11. Migrating certificates...\n";
$certs = $sourceDb->query("SELECT * FROM certificates")->fetchAll();
foreach ($certs as $cert) {
    $newUserId = $userMap[$cert['user_id']] ?? null;
    $newCourseId = $courseMap[$cert['course_id']] ?? null;
    if (!$newUserId || !$newCourseId) continue;

    if (!$dryRun) {
        // Check for duplicate
        $dup = $targetDb->prepare("SELECT id FROM certificates WHERE user_id = ? AND course_id = ?");
        $dup->execute([$newUserId, $newCourseId]);
        if ($dup->fetch()) continue;

        $stmt = $targetDb->prepare("
            INSERT INTO certificates (tenant_id, user_id, course_id, certificate_number,
                score_percentage, pdf_path, issued_at, revoked_at, revocation_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $newUserId,
            $newCourseId,
            $cert['certificate_number'],
            $cert['score_percentage'],
            $cert['pdf_s3_key'] ?? null,
            $cert['issued_at'],
            $cert['revoked'] ? ($cert['revoked_at'] ?? now()) : null,
            $cert['revoked_reason'] ?? null,
        ]);
    }
    progress('certificates', 1);
}
info("Migrated " . ($stats['certificates'] ?? 0) . " certificates");

// ============================================
// 12. Migrate Course Orders → Orders + Order Items
// ============================================

echo "\n12. Migrating course_orders → orders...\n";
$courseOrders = $sourceDb->query("SELECT * FROM course_orders")->fetchAll();
foreach ($courseOrders as $co) {
    // course_orders may not have user_id - look up by email if needed
    $newUserId = null;
    if (isset($co['user_id']) && $co['user_id']) {
        $newUserId = $userMap[$co['user_id']] ?? null;
    } elseif (!empty($co['email'])) {
        // Try to find mapped user by email
        foreach ($userMap as $oldId => $newId) {
            if (!$dryRun && $newId) {
                $u = $targetDb->prepare("SELECT id FROM users WHERE id = ? AND email = ?");
                $u->execute([$newId, $co['email']]);
                if ($u->fetch()) { $newUserId = $newId; break; }
            }
        }
    }

    // Map payment_status: completed → paid
    $paymentStatus = match($co['payment_status'] ?? 'pending') {
        'completed' => 'paid',
        'paid' => 'paid',
        'cancelled' => 'cancelled',
        'refunded' => 'refunded',
        default => 'pending',
    };
    $orderStatus = $paymentStatus === 'paid' ? 'paid' : 'pending';

    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO orders (tenant_id, order_number, customer_id, status,
                customer_email, customer_name, customer_phone, customer_company,
                billing_name, billing_address_line1,
                subtotal_dkk, tax_dkk, total_dkk,
                payment_method, payment_status, stripe_payment_intent_id,
                paid_at, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $totalAmount = $co['total_amount'] ?? $co['unit_price'] ?? 0;
        $vatAmount = $co['vat_amount'] ?? ($totalAmount * 0.2);
        $subtotal = $totalAmount - $vatAmount;

        $customerName = $co['billing_name'] ?? ($co['first_name'] ?? '') . ' ' . ($co['last_name'] ?? '');

        $stmt->execute([
            $tenantId,
            $co['order_number'],
            $newUserId,
            $orderStatus,
            $co['billing_email'] ?? $co['email'] ?? '',
            trim($customerName),
            $co['billing_phone'] ?? $co['phone'] ?? null,
            $co['company_name'] ?? $co['company_cvr'] ?? null,
            trim($customerName),
            $co['billing_address'] ?? null,
            $subtotal,
            $vatAmount,
            $totalAmount,
            ($co['payment_method'] ?? 'card') === 'invoice' ? 'invoice' : 'stripe',
            $paymentStatus === 'paid' ? 'paid' : 'unpaid',
            $co['stripe_payment_intent_id'] ?? null,
            $co['paid_at'] ?? null,
            $co['notes'] ?? null,
            $co['created_at'],
            $co['updated_at'],
        ]);
        $orderId = $targetDb->lastInsertId();
        $orderMap['course_' . $co['id']] = $orderId;

        // Create order item
        $newCourseId = $courseMap[$co['course_id']] ?? null;
        $courseName = $co['product_name'] ?? 'Course';
        $stmt2 = $targetDb->prepare("
            INSERT INTO order_items (order_id, name, quantity, unit_price_dkk, total_dkk)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt2->execute([
            $orderId,
            $courseName,
            $co['quantity'] ?? 1,
            $co['unit_price'] ?? $totalAmount,
            $totalAmount,
        ]);
    }
    progress('course_orders', 1);
}
info("Migrated " . ($stats['course_orders'] ?? 0) . " course orders");

// ============================================
// 13. Migrate Book Orders → Orders + Order Items
// ============================================

echo "\n13. Migrating book_orders → orders...\n";
$bookOrders = $sourceDb->query("SELECT * FROM book_orders")->fetchAll();
foreach ($bookOrders as $bo) {
    $paymentStatus = match($bo['payment_status'] ?? 'pending') {
        'paid' => 'paid',
        'refunded' => 'refunded',
        default => 'pending',
    };
    $orderStatus = $paymentStatus === 'paid' ? 'paid' : 'pending';

    if (!$dryRun) {
        $customerName = trim(($bo['first_name'] ?? '') . ' ' . ($bo['last_name'] ?? ''));
        $totalAmount = $bo['price'] ?? 0;
        $discount = $bo['discount_amount'] ?? 0;
        $subtotal = $totalAmount - ($totalAmount * 0.2); // rough VAT reverse
        $vat = $totalAmount * 0.2;

        $stmt = $targetDb->prepare("
            INSERT INTO orders (tenant_id, order_number, status,
                customer_email, customer_name, customer_phone, customer_company,
                subtotal_dkk, tax_dkk, discount_dkk, total_dkk,
                payment_method, payment_status, stripe_payment_intent_id,
                created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'stripe', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $bo['order_number'],
            $orderStatus,
            $bo['email'],
            $customerName,
            $bo['phone'] ?? null,
            $bo['company'] ?? null,
            $subtotal,
            $vat,
            $discount,
            $totalAmount,
            $paymentStatus === 'paid' ? 'paid' : 'unpaid',
            $bo['stripe_payment_intent_id'] ?? null,
            $bo['created_at'],
            $bo['updated_at'],
        ]);
        $orderId = $targetDb->lastInsertId();
        $orderMap['book_' . $bo['id']] = $orderId;

        // Create order item
        $stmt2 = $targetDb->prepare("
            INSERT INTO order_items (order_id, name, quantity, unit_price_dkk, total_dkk)
            VALUES (?, ?, 1, ?, ?)
        ");
        $stmt2->execute([
            $orderId,
            $bo['product_name'],
            $totalAmount,
            $totalAmount,
        ]);
    }
    progress('book_orders', 1);
}
info("Migrated " . ($stats['book_orders'] ?? 0) . " book orders");

// ============================================
// 14. Migrate Contact Messages
// ============================================

echo "\n14. Migrating contact_messages...\n";
$messages = $sourceDb->query("SELECT * FROM contact_messages")->fetchAll();
foreach ($messages as $msg) {
    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO contact_messages (tenant_id, name, email, subject, message, status,
                admin_reply, replied_at, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $msg['name'],
            $msg['email'],
            $msg['subject'] ?? 'Contact Form',
            $msg['message'],
            $msg['status'] ?? 'unread',
            $msg['reply_message'] ?? null,
            $msg['replied_at'] ?? null,
            $msg['ip_address'] ?? null,
            $msg['created_at'],
        ]);
    }
    progress('contact_messages', 1);
}
info("Migrated " . ($stats['contact_messages'] ?? 0) . " contact messages");

// ============================================
// 15. Migrate Consultation Bookings
// ============================================

echo "\n15. Migrating consultation_bookings...\n";
$bookings = $sourceDb->query("SELECT * FROM consultation_bookings")->fetchAll();
foreach ($bookings as $b) {
    if (!$dryRun) {
        $customerName = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? ''));
        $stmt = $targetDb->prepare("
            INSERT INTO consultation_bookings (tenant_id, booking_number,
                customer_name, customer_email, customer_phone, company,
                project_description, urgency, status, payment_status,
                stripe_payment_intent_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $status = match($b['booking_status'] ?? 'pending') {
            'confirmed' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'pending',
        };

        $payStatus = match($b['payment_status'] ?? 'pending') {
            'paid' => 'paid',
            'refunded' => 'refunded',
            default => 'unpaid',
        };

        $stmt->execute([
            $tenantId,
            $b['booking_number'],
            $customerName,
            $b['email'],
            $b['phone'] ?? null,
            $b['company_name'] ?? null,
            $b['message'] ?? null,
            $b['urgency'] ?? 'medium',
            $status,
            $payStatus,
            $b['stripe_payment_intent_id'] ?? null,
            $b['created_at'],
            $b['updated_at'],
        ]);
    }
    progress('consultations', 1);
}
info("Migrated " . ($stats['consultations'] ?? 0) . " consultation bookings");

// ============================================
// 16. Migrate Email Signups
// ============================================

echo "\n16. Migrating email_signups...\n";
$signups = $sourceDb->query("SELECT * FROM email_signups")->fetchAll();
foreach ($signups as $s) {
    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO email_signups (tenant_id, email, source_type, source_slug,
                ip_address, user_agent, created_at)
            VALUES (?, ?, 'lead_magnet', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $s['email'],
            $s['course_type'] ?? 'general',
            $s['ip_address'] ?? null,
            $s['user_agent'] ?? null,
            $s['created_at'],
        ]);
    }
    progress('email_signups', 1);
}
info("Migrated " . ($stats['email_signups'] ?? 0) . " email signups");

// ============================================
// 17. Migrate Newsletter Subscribers → Email Signups
// ============================================

echo "\n17. Migrating newsletter_subscribers → email_signups...\n";
$subscribers = $sourceDb->query("SELECT * FROM newsletter_subscribers WHERE is_active = 1")->fetchAll();
foreach ($subscribers as $sub) {
    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO email_signups (tenant_id, email, name, source_type, source_slug,
                ip_address, created_at)
            VALUES (?, ?, ?, 'newsletter', 'newsletter', ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $sub['email'],
            $sub['name'] ?? null,
            $sub['ip_address'] ?? null,
            $sub['subscribed_at'],
        ]);
    }
    progress('newsletter_subs', 1);
}
info("Migrated " . ($stats['newsletter_subs'] ?? 0) . " newsletter subscribers");

// ============================================
// 18. Insert Ebooks
// ============================================

echo "\n18. Creating ebooks...\n";
$ebooks = [
    [
        'slug' => 'chatgpt-atlas-guide',
        'title' => 'ChatGPT Atlas Guide',
        'subtitle' => 'Den komplette guide til ChatGPT',
        'description' => 'Den definitive guide til at mestre ChatGPT i dit professionelle liv.',
        'price_dkk' => 149.00,
        'pdf_filename' => 'atlas-final.pdf',
        'status' => 'published',
    ],
    [
        'slug' => 'linkedin-ai-mastery',
        'title' => 'LinkedIn AI Mastery',
        'subtitle' => 'Bliv LinkedIn-ekspert med AI',
        'description' => 'Lær at bruge AI til at dominere LinkedIn og bygge dit professionelle netværk.',
        'price_dkk' => 149.00,
        'pdf_filename' => 'LinkedIn-bog.pdf',
        'status' => 'published',
    ],
    [
        'slug' => 'ai-resultater-7-dage',
        'title' => 'AI Results in 7 Days',
        'subtitle' => 'Kom i gang med AI på 7 dage',
        'description' => 'En gratis guide til at opnå konkrete AI-resultater på kun 7 dage.',
        'price_dkk' => 0.00,
        'pdf_filename' => 'ai-resultater-7-dage.pdf.pdf',
        'status' => 'published',
    ],
    [
        'slug' => '300-ai-prompts',
        'title' => '300 AI Prompts',
        'subtitle' => '300 færdige AI-prompts til professionelle',
        'description' => 'En gratis samling af 300 professionelle AI-prompts klar til brug.',
        'price_dkk' => 0.00,
        'pdf_filename' => 'gratis-prompts.pdf',
        'status' => 'published',
    ],
];

foreach ($ebooks as $eb) {
    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO ebooks (tenant_id, slug, title, subtitle, description,
                price_dkk, pdf_filename, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tenantId,
            $eb['slug'],
            $eb['title'],
            $eb['subtitle'],
            $eb['description'],
            $eb['price_dkk'],
            $eb['pdf_filename'],
            $eb['status'],
        ]);
    }
    info("Ebook: {$eb['title']}");
    progress('ebooks', 1);
}

// ============================================
// 19. Insert Lead Magnets
// ============================================

echo "\n19. Creating lead magnets...\n";
$leadMagnets = [
    [
        'slug' => 'free-atlas',
        'title' => 'Free ChatGPT Atlas Preview',
        'hero_headline' => 'Download gratis ChatGPT Atlas Preview',
        'hero_subheadline' => 'Få en smagsprøve på den komplette ChatGPT guide',
        'pdf_filename' => 'chatgpt_atlas_guide_FREE.pdf',
        'hero_bg_color' => '#1e40af',
    ],
    [
        'slug' => 'free-ai-prompts',
        'title' => '300 Free AI Prompts',
        'hero_headline' => 'Download 300 gratis AI-prompts',
        'hero_subheadline' => 'Professionelle prompts klar til brug i dit arbejde',
        'pdf_filename' => 'gratis-prompts.pdf',
        'hero_bg_color' => '#065f46',
    ],
    [
        'slug' => 'free-ai-tools',
        'title' => '10 AI Tools for Consultants',
        'hero_headline' => '10 AI-værktøjer til konsulenter',
        'hero_subheadline' => 'De bedste AI-værktøjer til at effektivisere din konsulentvirksomhed',
        'pdf_filename' => '10-ai-vaerktojer-konsulent.pdf',
        'hero_bg_color' => '#7c3aed',
    ],
    [
        'slug' => 'free-udlaeg',
        'title' => 'AI Expense Templates',
        'hero_headline' => 'Gratis AI-skabeloner til udlæg',
        'hero_subheadline' => 'Automatiser din udlægshåndtering med AI',
        'pdf_filename' => 'udlaeg-ai.pdf',
        'hero_bg_color' => '#b45309',
    ],
];

foreach ($leadMagnets as $lm) {
    if (!$dryRun) {
        $stmt = $targetDb->prepare("
            INSERT INTO lead_magnets (tenant_id, slug, title,
                hero_headline, hero_subheadline, hero_bg_color,
                pdf_filename, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'published')
        ");
        $stmt->execute([
            $tenantId,
            $lm['slug'],
            $lm['title'],
            $lm['hero_headline'],
            $lm['hero_subheadline'],
            $lm['hero_bg_color'],
            $lm['pdf_filename'],
        ]);
    }
    info("Lead Magnet: {$lm['title']}");
    progress('lead_magnets', 1);
}

// ============================================
// 20. Update course totals
// ============================================

echo "\n20. Updating course totals...\n";
if (!$dryRun) {
    foreach ($courseMap as $oldId => $newId) {
        // Count total lessons
        $stmt = $targetDb->prepare("
            SELECT COUNT(*) as cnt FROM course_lessons WHERE course_id = ?
        ");
        $stmt->execute([$newId]);
        $totalLessons = $stmt->fetch()['cnt'];

        // Sum total duration
        $stmt = $targetDb->prepare("
            SELECT COALESCE(SUM(video_duration_seconds), 0) as total FROM course_lessons WHERE course_id = ?
        ");
        $stmt->execute([$newId]);
        $totalDuration = $stmt->fetch()['total'];

        $targetDb->prepare("
            UPDATE courses SET total_lessons = ?, total_duration_seconds = ? WHERE id = ?
        ")->execute([$totalLessons, $totalDuration, $newId]);

        info("Course #{$newId}: {$totalLessons} lessons, {$totalDuration}s total");
    }
}

// ============================================
// Summary
// ============================================

echo "\n=== Migration Summary ===\n";
foreach ($stats as $type => $count) {
    echo "  {$type}: {$count}\n";
}
echo "\n";

if ($dryRun) {
    echo "DRY RUN complete. No data was written.\n";
    echo "Run without --dry-run to perform actual migration.\n";
} else {
    echo "Migration complete!\n";
    echo "\nNext steps:\n";
    echo "  1. Copy assets (images, PDFs) to tenant directories\n";
    echo "  2. Create custom pages for marketing content\n";
    echo "  3. Configure DNS for aibootcamphq.com\n";
}
