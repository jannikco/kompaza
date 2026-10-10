<?php

namespace App\Services\LinkedIn;

/**
 * Flattens a Voyager profile-updates (or feed) response into post cards.
 *
 * Same normalized+json+2.1 graph as CommentParser: `data` holds references,
 * `included[]` holds activities + socialDetail counts.
 */
final class RecentPostsParser
{
    /**
     * @return list<array{
     *   post_urn:string, share_url:?string, commentary:string,
     *   created_at:?string, comments:int, reactions:int, reposts:int
     * }>
     */
    public static function parse(array $response): array
    {
        $index = [];
        foreach ($response['included'] ?? [] as $element) {
            if (!is_array($element) || empty($element['entityUrn'])) {
                continue;
            }
            $index[$element['entityUrn']] = $element;
        }

        $posts = [];
        $seen  = [];

        foreach (self::candidateElements($response, $index) as $element) {
            $urn = self::activityUrn($element);
            if ($urn === null || isset($seen[$urn])) {
                continue;
            }
            $seen[$urn] = true;

            $social = self::socialCounts($element, $index);
            $posts[] = [
                'post_urn'   => $urn,
                'share_url'  => self::shareUrl($element, $urn),
                'commentary' => self::commentary($element),
                'created_at' => self::timestamp($element),
                'comments'   => $social['comments'],
                'reactions'  => $social['reactions'],
                'reposts'    => $social['reposts'],
            ];
        }

        return $posts;
    }

    /** @return list<array> */
    private static function candidateElements(array $response, array $index): array
    {
        $out = [];
        foreach (['*elements', 'elements'] as $key) {
            foreach ((array) ($response['data'][$key] ?? []) as $ref) {
                if (is_string($ref) && isset($index[$ref])) {
                    $out[] = $index[$ref];
                } elseif (is_array($ref)) {
                    $out[] = $ref;
                }
            }
        }
        foreach ((array) ($response['elements'] ?? []) as $element) {
            if (is_array($element)) {
                $out[] = $element;
            }
        }
        foreach ($index as $element) {
            if (self::activityUrn($element) !== null) {
                $out[] = $element;
            }
        }
        return $out;
    }

    private static function activityUrn(array $element): ?string
    {
        foreach (['entityUrn', 'dashEntityUrn', 'urn', '*socialDetail'] as $key) {
            $value = $element[$key] ?? null;
            if (is_string($value) && preg_match('/urn:li:(?:activity|ugcPost|share):\d+/', $value, $m)) {
                return $m[0];
            }
        }
        $update = $element['updateMetadata']['urn'] ?? $element['updateMetadata']['shareUrn'] ?? null;
        if (is_string($update) && str_starts_with($update, 'urn:li:')) {
            return $update;
        }
        return null;
    }

    private static function commentary(array $element): string
    {
        $candidates = [
            $element['commentary']['text']['text'] ?? null,
            $element['commentary']['text'] ?? null,
            $element['commentary'] ?? null,
            $element['value']['com.linkedin.voyager.feed.render.UpdateV2']['commentary']['text']['text'] ?? null,
            $element['snippet'] ?? null,
        ];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }
        return '';
    }

    private static function shareUrl(array $element, string $urn): ?string
    {
        $url = $element['socialContent']['shareUrl']
            ?? $element['updateMetadata']['updateActions']['shareVia']
            ?? null;
        if (is_string($url) && str_contains($url, 'linkedin.com')) {
            return $url;
        }
        if (preg_match('/urn:li:activity:(\d+)/', $urn, $m)) {
            return 'https://www.linkedin.com/feed/update/urn:li:activity:' . $m[1] . '/';
        }
        if (preg_match('/urn:li:ugcPost:(\d+)/', $urn, $m)) {
            return 'https://www.linkedin.com/feed/update/urn:li:ugcPost:' . $m[1] . '/';
        }
        return null;
    }

    private static function timestamp(array $element): ?string
    {
        $ms = $element['actor']['subDescription']['accessibilityText']
            ?? $element['createdAt']
            ?? $element['publishedAt']
            ?? $element['createdTime']
            ?? null;
        if (is_numeric($ms)) {
            return date('Y-m-d H:i:s', (int) ($ms / 1000));
        }
        return null;
    }

    /** @return array{comments:int,reactions:int,reposts:int} */
    private static function socialCounts(array $element, array $index): array
    {
        $detail = $element['socialDetail'] ?? $element['*socialDetail'] ?? null;
        if (is_string($detail) && isset($index[$detail])) {
            $detail = $index[$detail];
        }
        $counts = is_array($detail)
            ? ($detail['totalSocialActivityCounts'] ?? $detail)
            : [];

        return [
            'comments'  => (int) ($counts['numComments'] ?? $counts['comments'] ?? 0),
            'reactions' => (int) ($counts['numLikes'] ?? $counts['numReactions'] ?? $counts['likes'] ?? 0),
            'reposts'   => (int) ($counts['numShares'] ?? $counts['reposts'] ?? 0),
        ];
    }
}
