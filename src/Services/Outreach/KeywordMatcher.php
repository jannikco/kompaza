<?php

namespace App\Services\Outreach;

final class KeywordMatcher
{
    /**
     * @param list<string>|string|null $keywords
     * @return string|null the first keyword that matched, or null
     */
    public static function firstMatch(string $text, array|string|null $keywords): ?string
    {
        foreach (self::normalize($keywords) as $keyword) {
            if (mb_stripos($text, $keyword) !== false) {
                return $keyword;
            }
        }
        return null;
    }

    /**
     * @param list<string>|string|null $keywords
     * @return list<string>
     */
    public static function normalize(array|string|null $keywords): array
    {
        if ($keywords === null || $keywords === '') {
            return [];
        }
        if (is_string($keywords)) {
            $trimmed = trim($keywords);
            if ($trimmed !== '' && ($trimmed[0] ?? '') === '[') {
                $decoded = json_decode($trimmed, true);
                $keywords = is_array($decoded) ? $decoded : (preg_split('/[,;\n]+/', $trimmed) ?: []);
            } else {
                $keywords = preg_split('/[,;\n]+/', $trimmed) ?: [];
            }
        }

        $out = [];
        foreach ($keywords as $keyword) {
            if (!is_string($keyword) && !is_numeric($keyword)) {
                continue;
            }
            $keyword = trim((string) $keyword);
            if ($keyword === '') {
                continue;
            }
            $out[] = $keyword;
        }
        return array_values(array_unique($out));
    }
}
