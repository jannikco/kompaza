<?php

namespace App\Models;

use App\Database\Database;

class PageView {
    public static function record($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO page_views (tenant_id, page_type, page_id, page_url, visitor_id, user_id, referrer, utm_source, utm_medium, utm_campaign, device_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['page_type'],
            $data['page_id'] ?? null,
            $data['page_url'],
            $data['visitor_id'] ?? null,
            $data['user_id'] ?? null,
            $data['referrer'] ?? null,
            $data['utm_source'] ?? null,
            $data['utm_medium'] ?? null,
            $data['utm_campaign'] ?? null,
            $data['device_type'] ?? null,
        ]);
    }

    /**
     * Get MRR (Monthly Recurring Revenue) - revenue from current month
     */
    public static function getMRR($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(total_dkk), 0) as mrr
            FROM orders
            WHERE tenant_id = ? AND status IN ('paid','completed','processing')
            AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ");
        $stmt->execute([$tenantId]);
        return (float)$stmt->fetch()['mrr'];
    }

    /**
     * Get revenue by month for the last N months
     */
    public static function getRevenueByMonth($tenantId, $months = 12) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   COALESCE(SUM(total_dkk), 0) as revenue,
                   COUNT(*) as order_count
            FROM orders
            WHERE tenant_id = ? AND status IN ('paid','completed','processing')
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute([$tenantId, $months]);
        return $stmt->fetchAll();
    }

    /**
     * Get Customer Lifetime Value (average revenue per customer)
     */
    public static function getCLV($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(AVG(customer_total), 0) as clv
            FROM (
                SELECT customer_id, SUM(total_dkk) as customer_total
                FROM orders
                WHERE tenant_id = ? AND status IN ('paid','completed','processing') AND customer_id IS NOT NULL
                GROUP BY customer_id
            ) sub
        ");
        $stmt->execute([$tenantId]);
        return (float)$stmt->fetch()['clv'];
    }

    /**
     * Get churn rate (customers who haven't ordered in last 90 days vs total)
     */
    public static function getChurnRate($tenantId) {
        $db = Database::getConnection();
        // Total customers who have ever ordered
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT customer_id) as total
            FROM orders WHERE tenant_id = ? AND customer_id IS NOT NULL AND status IN ('paid','completed','processing')
        ");
        $stmt->execute([$tenantId]);
        $total = (int)$stmt->fetch()['total'];
        if ($total === 0) return 0;

        // Active customers (ordered in last 90 days)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT customer_id) as active
            FROM orders WHERE tenant_id = ? AND customer_id IS NOT NULL AND status IN ('paid','completed','processing')
            AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute([$tenantId]);
        $active = (int)$stmt->fetch()['active'];

        $churned = $total - $active;
        return round(($churned / $total) * 100, 1);
    }

    /**
     * Get revenue by product
     */
    public static function getRevenueByProduct($tenantId, $limit = 10) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.name as product_name, p.id as product_id,
                   COUNT(DISTINCT o.id) as order_count,
                   COALESCE(SUM(oi.price_dkk * oi.quantity), 0) as revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.tenant_id = ? AND o.status IN ('paid','completed','processing')
            GROUP BY p.id, p.name
            ORDER BY revenue DESC
            LIMIT ?
        ");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get conversion funnel: visitors -> signups -> customers -> orders
     */
    public static function getConversionFunnel($tenantId, $days = 30) {
        $db = Database::getConnection();

        // Page views
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM page_views WHERE tenant_id = ? AND viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$tenantId, $days]);
        $views = (int)$stmt->fetch()['count'];

        // Email signups
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_signups WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$tenantId, $days]);
        $signups = (int)$stmt->fetch()['count'];

        // New customers
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$tenantId, $days]);
        $customers = (int)$stmt->fetch()['count'];

        // Orders
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND status IN ('paid','completed','processing') AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$tenantId, $days]);
        $orders = (int)$stmt->fetch()['count'];

        return [
            'views' => $views,
            'signups' => $signups,
            'customers' => $customers,
            'orders' => $orders,
        ];
    }

    /**
     * Get top pages by views
     */
    public static function getTopPages($tenantId, $days = 30, $limit = 10) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT page_type, page_id, page_url, COUNT(*) as views, COUNT(DISTINCT visitor_id) as unique_visitors
            FROM page_views
            WHERE tenant_id = ? AND viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY page_type, page_id, page_url
            ORDER BY views DESC
            LIMIT ?
        ");
        $stmt->execute([$tenantId, $days, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get traffic sources
     */
    public static function getTrafficSources($tenantId, $days = 30) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                CASE
                    WHEN utm_source IS NOT NULL THEN utm_source
                    WHEN referrer IS NOT NULL AND referrer != '' THEN SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '/', 3), '//', -1)
                    ELSE 'Direct'
                END as source,
                COUNT(*) as views
            FROM page_views
            WHERE tenant_id = ? AND viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY source
            ORDER BY views DESC
            LIMIT 10
        ");
        $stmt->execute([$tenantId, $days]);
        return $stmt->fetchAll();
    }

    /**
     * Get new customers and subscribers by day for chart
     */
    public static function getDailyGrowth($tenantId, $days = 30) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users
            WHERE tenant_id = ? AND role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$tenantId, $days]);
        $customers = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM email_signups
            WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$tenantId, $days]);
        $subscribers = $stmt->fetchAll();

        return ['customers' => $customers, 'subscribers' => $subscribers];
    }
}
