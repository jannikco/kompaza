<?php

namespace App\Services\LinkedIn;

/**
 * Parses a Voyager comments response into flat, stable comment records.
 *
 * LinkedInService sends `Accept: application/vnd.linkedin.normalized+json+2.1`, so
 * the response is a GRAPH, not a tree:
 *
 *     {
 *       "data":     { "*elements": ["urn:li:comment:(...)", ...] },
 *       "included": [ {"entityUrn": "...", "$type": "...", ...}, ... ]
 *     }
 *
 * `data` holds URN *references*; `included[]` holds the actual entities. Keys
 * prefixed with `*` are references that must be looked up in `included[]`.
 *
 * The previous inline parser treated `included[]` as a flat list of comments and
 * never dereferenced anything, so it only ever saw fields that happened to be
 * inlined. In particular it read the commenter's public identifier one level too
 * high (`$element['commenterProfileId']` rather than
 * `$element['commenter']['commenterProfileId']`), which meant `commenter_profile_url`
 * came back empty almost every time — and since `LinkedInService::fetchProfile()`
 * can only consume a public identifier, nothing downstream could ever enrich a
 * commenter.
 *
 * This class is deliberately pure and static: no cURL, no database, no session. It
 * is the first LinkedIn code in this codebase that can be tested against a saved
 * fixture.
 */
final class CommentParser
{
    /**
     * @param array $response Decoded Voyager response
     * @return array{comments: list<array>, resolved: int, unresolved: int}
     */
    public static function parse(array $response): array
    {
        $index = self::indexIncluded($response['included'] ?? []);

        $comments   = [];
        $resolved   = 0;
        $unresolved = 0;

        foreach (self::collectCommentElements($response, $index) as $element) {
            $commentUrn = $element['entityUrn'] ?? $element['commentUrn'] ?? $element['urn'] ?? null;
            if (!$commentUrn) {
                continue;
            }

            $author = self::resolveAuthor($element, $index);
            if ($author['public_id'] !== null) {
                $resolved++;
            } else {
                $unresolved++;
            }

            $comments[] = [
                'comment_urn'            => $commentUrn,
                'comment_text'           => self::commentText($element),
                'commented_at'           => self::timestamp($element),
                'commenter_urn'          => $author['profile_urn'],
                'commenter_member_urn'   => $author['member_urn'],
                'commenter_public_id'    => $author['public_id'],
                'commenter_name'         => $author['name'],
                'commenter_headline'     => $author['headline'],
                'commenter_occupation'   => $author['occupation'],
                'commenter_image_url'    => $author['image_url'],
                'commenter_distance'     => $author['distance'],
                'commenter_profile_url'  => $author['public_id']
                    ? 'https://www.linkedin.com/in/' . $author['public_id']
                    : '',
            ];
        }

        return ['comments' => $comments, 'resolved' => $resolved, 'unresolved' => $unresolved];
    }

    // ---------------------------------------------------------------------
    // Graph
    // ---------------------------------------------------------------------

    /** @return array<string,array> entityUrn => element */
    private static function indexIncluded(array $included): array
    {
        $index = [];
        foreach ($included as $element) {
            if (!is_array($element)) {
                continue;
            }
            if (!empty($element['entityUrn'])) {
                $index[$element['entityUrn']] = $element;
            }
            // Some profile entities are addressable by their member URN too.
            if (!empty($element['objectUrn'])) {
                $index[$element['objectUrn']] = $element;
            }
        }
        return $index;
    }

    /**
     * Every element that looks like a comment, from whichever shape this response uses.
     *
     * @return list<array>
     */
    private static function collectCommentElements(array $response, array $index): array
    {
        $out  = [];
        $seen = [];

        $push = static function ($element) use (&$out, &$seen) {
            if (!is_array($element)) {
                return;
            }
            $key = $element['entityUrn'] ?? $element['commentUrn'] ?? $element['urn'] ?? null;
            if ($key !== null) {
                if (isset($seen[$key])) {
                    return;
                }
                $seen[$key] = true;
            }
            $out[] = $element;
        };

        // 1. Reference lists in `data` -> dereference through the index.
        foreach (['*elements', 'elements', '*comments'] as $key) {
            foreach ((array)($response['data'][$key] ?? []) as $ref) {
                if (is_string($ref) && isset($index[$ref])) {
                    $push($index[$ref]);
                } elseif (is_array($ref)) {
                    $push($ref);
                }
            }
        }

        // 2. Inline elements at the top level (older shape).
        foreach ((array)($response['elements'] ?? []) as $element) {
            $push($element);
        }

        // 3. Anything in `included[]` that is itself a comment. This is the shape the
        //    old parser relied on exclusively.
        foreach ($index as $element) {
            if (self::looksLikeComment($element)) {
                $push($element);
            }
        }

        return $out;
    }

    private static function looksLikeComment(array $element): bool
    {
        $type = (string)($element['$type'] ?? $element['$recipeType'] ?? '');
        if ($type !== '' && (str_contains($type, 'social.Comment') || str_contains($type, 'feed.Comment'))) {
            return true;
        }
        // Fall back on shape when $type is absent.
        return isset($element['commentary']) || isset($element['commentV2']) || isset($element['commenter']);
    }

    // ---------------------------------------------------------------------
    // Fields
    // ---------------------------------------------------------------------

    private static function commentText(array $element): string
    {
        $candidates = [
            $element['commentary'] ?? null,
            $element['comment']['values'][0]['value'] ?? null,
            $element['commentV2']['text'] ?? null,
            $element['commentV2'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $text = self::flattenText($candidate);
            if ($text !== '') {
                return $text;
            }
        }
        return '';
    }

    /** Voyager wraps text as a bare string, {text: "..."} , or {text: {text: "..."}}. */
    private static function flattenText($value): string
    {
        if (is_string($value)) {
            return trim($value);
        }
        if (is_array($value)) {
            if (isset($value['text'])) {
                return self::flattenText($value['text']);
            }
            if (isset($value['values'][0]['value'])) {
                return self::flattenText($value['values'][0]['value']);
            }
        }
        return '';
    }

    private static function timestamp(array $element): ?string
    {
        $ms = $element['createdTime'] ?? $element['createdAt'] ?? $element['time'] ?? null;
        if (!is_numeric($ms)) {
            return null;
        }
        // Voyager reports milliseconds.
        return date('Y-m-d H:i:s', (int)($ms / 1000));
    }

    /**
     * Resolve the comment's author through the graph.
     *
     * @return array{profile_urn:?string, member_urn:?string, public_id:?string,
     *               name:string, headline:string, occupation:?string,
     *               image_url:?string, distance:?string}
     */
    private static function resolveAuthor(array $element, array $index): array
    {
        $out = [
            'profile_urn' => null, 'member_urn' => null, 'public_id' => null,
            'name' => '', 'headline' => '', 'occupation' => null,
            'image_url' => null, 'distance' => null,
        ];

        $commenter = $element['commenter'] ?? $element['actor'] ?? [];
        if (!is_array($commenter)) {
            $commenter = [];
        }

        // Candidate references to the author entity, most specific first.
        $refs = [
            $commenter['*author'] ?? null,
            $commenter['author'] ?? null,
            $commenter['*miniProfile'] ?? null,
            $commenter['miniProfile'] ?? null,
            $commenter['commenterProfileId'] ?? null,
            $commenter['memberProfileUrn'] ?? null,
            $commenter['urn'] ?? null,
            $commenter['entityUrn'] ?? null,
            $element['*author'] ?? null,
        ];

        $profile = null;
        foreach ($refs as $ref) {
            if (is_array($ref)) {
                // Already inlined.
                $profile = $ref;
                break;
            }
            if (is_string($ref) && $ref !== '') {
                if (isset($index[$ref])) {
                    $profile = $index[$ref];
                    break;
                }
                // Keep the first URN-shaped reference even if we cannot dereference it,
                // so sendMessage() still has something to address.
                if ($out['profile_urn'] === null && str_starts_with($ref, 'urn:li:')) {
                    $out['profile_urn'] = $ref;
                }
            }
        }

        // The commenter node itself may carry the fields inline.
        $sources = array_filter([$profile, $commenter], 'is_array');

        foreach ($sources as $src) {
            // `commenterProfileId` is overloaded in Voyager: on the dash shape it is a
            // profile URN, on the legacy shape it is the vanity slug. Accept it as a
            // public identifier only when it is clearly not a URN. Conflating the two is
            // exactly what broke the original parser.
            $out['public_id'] ??= self::firstSlug($src, [
                'publicIdentifier', 'publicId', 'vanityName', 'commenterProfileId',
            ]);
            $out['occupation']  ??= self::firstNonEmpty($src, ['occupation', 'headline']);
            $out['image_url']   ??= self::pictureUrl($src);

            if ($out['profile_urn'] === null || !str_contains((string)$out['profile_urn'], 'fsd_profile')) {
                $urn = self::firstNonEmpty($src, ['entityUrn', 'dashEntityUrn']);
                if ($urn !== null && str_contains($urn, 'fsd_profile')) {
                    $out['profile_urn'] = $urn;
                }
            }
            if ($out['member_urn'] === null) {
                $member = self::firstNonEmpty($src, ['objectUrn']);
                if ($member !== null && str_contains($member, ':member:')) {
                    $out['member_urn'] = $member;
                }
            }
            if ($out['distance'] === null) {
                $distance = $src['distance']['value'] ?? $src['distance'] ?? null;
                $out['distance'] = is_string($distance) ? $distance : null;
            }
            if ($out['name'] === '') {
                $out['name'] = self::personName($src);
            }
            if ($out['headline'] === '') {
                $out['headline'] = self::flattenText(
                    $src['headline'] ?? $src['occupation'] ?? $src['description'] ?? null
                );
            }
        }

        // Last resort for the URN so a DM can still be addressed.
        if ($out['profile_urn'] === null) {
            $out['profile_urn'] = $out['member_urn'];
        }

        return $out;
    }

    private static function personName(array $src): string
    {
        $first = self::flattenText($src['firstName'] ?? null);
        $last  = self::flattenText($src['lastName'] ?? null);
        $name  = trim($first . ' ' . $last);
        if ($name !== '') {
            return $name;
        }
        return self::flattenText($src['name'] ?? $src['title'] ?? null);
    }

    private static function pictureUrl(array $src): ?string
    {
        $root = $src['profilePicture']['displayImageReference']['vectorImage']['rootUrl']
            ?? $src['picture']['rootUrl']
            ?? null;
        $segment = $src['profilePicture']['displayImageReference']['vectorImage']['artifacts'][0]['fileIdentifyingUrlPathSegment']
            ?? $src['picture']['artifacts'][0]['fileIdentifyingUrlPathSegment']
            ?? null;

        if (is_string($root) && is_string($segment)) {
            return $root . $segment;
        }
        return null;
    }

    /** @param list<string> $keys */
    private static function firstNonEmpty(array $src, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!empty($src[$key]) && is_string($src[$key])) {
                return $src[$key];
            }
        }
        return null;
    }

    /**
     * Like firstNonEmpty(), but only accepts values that look like a vanity slug —
     * never a URN. `fetchProfile()` takes a slug and silently misbehaves on a URN,
     * so this distinction has to be enforced here rather than at the call site.
     *
     * @param list<string> $keys
     */
    private static function firstSlug(array $src, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $src[$key] ?? null;
            if (!is_string($value) || $value === '') {
                continue;
            }
            if (str_starts_with($value, 'urn:') || str_contains($value, ':')) {
                continue;
            }
            return $value;
        }
        return null;
    }
}
