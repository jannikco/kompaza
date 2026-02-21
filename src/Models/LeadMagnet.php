<?php

namespace App\Models;

use App\Database\Database;

class LeadMagnet {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM lead_magnets WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM lead_magnets WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM lead_magnets WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM lead_magnets WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM lead_magnets WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO lead_magnets (tenant_id, slug, title, subtitle, meta_description, hero_headline, hero_subheadline, hero_cta_text, hero_bg_color, hero_image_path, cover_image_path, features_headline, features, chapters, key_statistics, target_audience, faq, before_after, author_bio, testimonial_templates, social_proof, pdf_filename, pdf_original_name, email_subject, email_body_html, brevo_list_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['slug'],
            $data['title'],
            $data['subtitle'] ?? null,
            $data['meta_description'] ?? null,
            $data['hero_headline'] ?? null,
            $data['hero_subheadline'] ?? null,
            $data['hero_cta_text'] ?? 'Download Free',
            $data['hero_bg_color'] ?? '#1e40af',
            $data['hero_image_path'] ?? null,
            $data['cover_image_path'] ?? null,
            $data['features_headline'] ?? null,
            $data['features'] ?? null,
            $data['chapters'] ?? null,
            $data['key_statistics'] ?? null,
            $data['target_audience'] ?? null,
            $data['faq'] ?? null,
            $data['before_after'] ?? null,
            $data['author_bio'] ?? null,
            $data['testimonial_templates'] ?? null,
            $data['social_proof'] ?? null,
            $data['pdf_filename'] ?? null,
            $data['pdf_original_name'] ?? null,
            $data['email_subject'] ?? null,
            $data['email_body_html'] ?? null,
            $data['brevo_list_id'] ?? null,
            $data['status'] ?? 'draft',
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE lead_magnets SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM lead_magnets WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE lead_magnets SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementSignups($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE lead_magnets SET signup_count = signup_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM lead_magnets WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
