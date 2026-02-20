<?php

namespace App\Services;

use App\Models\Certificate;

class CertificateService {
    /**
     * Generate a certificate HTML and save as a simple HTML file (can be printed to PDF by browser).
     * Uses HTML/CSS-based certificate design - no external PDF library needed.
     */
    public static function generateHtml($certificate, $user, $course, $tenant) {
        $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Kompaza';
        $primaryColor = $tenant['primary_color'] ?? '#3b82f6';
        $issuedDate = date('F j, Y', strtotime($certificate['issued_at']));
        $verifyUrl = url('certificate/verify/' . $certificate['certificate_number']);

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Certificate - ' . htmlspecialchars($user['name']) . '</title>
<style>
@page { size: landscape A4; margin: 0; }
body { margin: 0; padding: 0; font-family: Georgia, "Times New Roman", serif; }
.certificate { width: 297mm; height: 210mm; position: relative; background: #fff; border: 8px solid ' . htmlspecialchars($primaryColor) . '; box-sizing: border-box; display: flex; align-items: center; justify-content: center; }
.inner { border: 2px solid ' . htmlspecialchars($primaryColor) . '; margin: 15mm; padding: 20mm; width: calc(100% - 30mm); height: calc(100% - 30mm); box-sizing: border-box; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.company { font-size: 14pt; color: #666; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5mm; }
.title { font-size: 36pt; color: ' . htmlspecialchars($primaryColor) . '; margin-bottom: 5mm; font-weight: bold; }
.subtitle { font-size: 14pt; color: #888; margin-bottom: 8mm; }
.name { font-size: 28pt; color: #1a1a1a; border-bottom: 2px solid #ddd; padding-bottom: 3mm; margin-bottom: 8mm; display: inline-block; padding-left: 20mm; padding-right: 20mm; }
.course { font-size: 16pt; color: #333; margin-bottom: 3mm; }
.course-title { font-size: 20pt; color: ' . htmlspecialchars($primaryColor) . '; font-weight: bold; margin-bottom: 8mm; }
.score { font-size: 12pt; color: #666; margin-bottom: 8mm; }
.meta { font-size: 10pt; color: #999; margin-top: auto; }
.meta span { margin: 0 10mm; }
@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="certificate">
<div class="inner">
    <div class="company">' . htmlspecialchars($companyName) . '</div>
    <div class="title">Certificate of Completion</div>
    <div class="subtitle">This is to certify that</div>
    <div class="name">' . htmlspecialchars($user['name']) . '</div>
    <div class="course">has successfully completed the course</div>
    <div class="course-title">' . htmlspecialchars($course['title']) . '</div>
    ' . ($certificate['score_percentage'] ? '<div class="score">with a score of ' . number_format($certificate['score_percentage'], 1) . '%</div>' : '') . '
    <div class="meta">
        <span>Issued: ' . $issuedDate . '</span>
        <span>Certificate #: ' . htmlspecialchars($certificate['certificate_number']) . '</span>
    </div>
    <div class="meta" style="margin-top: 3mm;">
        <span>Verify at: ' . htmlspecialchars($verifyUrl) . '</span>
    </div>
</div>
</div>
</body>
</html>';

        return $html;
    }
}
