<?php
/**
 * CommentParser — Voyager comment-graph parsing.
 *
 * The point of these fixtures: LinkedIn returns `application/vnd.linkedin.normalized
 * +json+2.1`, which is a GRAPH — `data` holds URN references and `included[]` holds
 * the entities. The parser this replaced treated `included[]` as a flat list and read
 * the commenter's public identifier one level too high, so it returned an empty
 * profile URL for essentially every commenter. Since fetchProfile() can only consume
 * a public identifier, that made enrichment impossible.
 *
 * Fixture 1 asserts the new parser resolves what the old one could not, and pins the
 * old behaviour alongside it so the regression is visible rather than described.
 */

require_once dirname(__DIR__, 2) . '/src/Services/LinkedIn/CommentParser.php';

use App\Services\LinkedIn\CommentParser;

/** The previous inline parser, transcribed from connectpilot-post-runner.php. */
$oldParser = static function (array $response): array {
    $comments = [];
    $elements = $response['elements'] ?? $response['data']['elements'] ?? $response['included'] ?? [];
    foreach ($elements as $element) {
        $commentUrn = $element['entityUrn'] ?? $element['commentUrn'] ?? $element['urn'] ?? null;
        if (!$commentUrn) continue;
        $profileUrl = '';
        $commenterUrn = null;
        if (isset($element['commenter'])) {
            $c = $element['commenter'];
            $commenterUrn = $c['commenterProfileId'] ?? $c['memberProfileUrn'] ?? $c['urn'] ?? null;
        }
        if ($commenterUrn) {
            $publicId = $element['commenterProfileId'] ?? null;   // one level too high
            if ($publicId) $profileUrl = 'https://www.linkedin.com/in/' . $publicId;
        }
        $comments[] = ['commenter_profile_url' => $profileUrl];
    }
    return $comments;
};

// ---------------------------------------------------------------------------
section('normalized graph (the shape production returns)');

$graph = [
    'data' => ['*elements' => [
        'urn:li:fsd_comment:(7231,urn:li:activity:7220)',
        'urn:li:fsd_comment:(7232,urn:li:activity:7220)',
    ]],
    'included' => [
        [
            'entityUrn' => 'urn:li:fsd_profile:ACoAAABmette',
            'objectUrn' => 'urn:li:member:99887766',
            '$type'     => 'com.linkedin.voyager.dash.identity.profile.Profile',
            'publicIdentifier' => 'mette-soerensen',
            'firstName' => 'Mette', 'lastName' => 'Sørensen',
            'headline'  => 'Director, Digital Health hos Novo Nordisk',
            'distance'  => ['value' => 'DISTANCE_2'],
            'profilePicture' => ['displayImageReference' => ['vectorImage' => [
                'rootUrl'   => 'https://media.licdn.com/dms/image/x/',
                'artifacts' => [['fileIdentifyingUrlPathSegment' => 'mette_100.jpg']],
            ]]],
        ],
        [
            'entityUrn' => 'urn:li:fsd_profile:ACoAAABlars',
            'objectUrn' => 'urn:li:member:11223344',
            'publicIdentifier' => 'lars-bo-nielsen',
            'firstName' => 'Lars Bo', 'lastName' => 'Nielsen',
            'headline'  => 'Selvstændig AI-konsulent',
        ],
        [
            'entityUrn'   => 'urn:li:fsd_comment:(7231,urn:li:activity:7220)',
            '$type'       => 'com.linkedin.voyager.dash.social.Comment',
            'commentary'  => ['text' => 'GUIDE — vi kæmper med governance lige nu'],
            'createdTime' => 1754300000000,
            'commenter'   => ['*author' => 'urn:li:fsd_profile:ACoAAABmette'],
        ],
        [
            'entityUrn'  => 'urn:li:fsd_comment:(7232,urn:li:activity:7220)',
            '$type'      => 'com.linkedin.voyager.dash.social.Comment',
            'commentary' => ['text' => 'GUIDE'],
            'commenter'  => ['*author' => 'urn:li:fsd_profile:ACoAAABlars'],
        ],
    ],
];

$result = CommentParser::parse($graph);
$mette  = null;
foreach ($result['comments'] as $c) {
    if (str_contains($c['comment_urn'], '7231')) { $mette = $c; }
}

ok('finds both comments', count($result['comments']) === 2, (string)count($result['comments']));
ok('resolves publicIdentifier through the graph', ($mette['commenter_public_id'] ?? null) === 'mette-soerensen', var_export($mette['commenter_public_id'] ?? null, true));
ok('builds a fetchProfile-usable URL', ($mette['commenter_profile_url'] ?? '') === 'https://www.linkedin.com/in/mette-soerensen');
ok('resolves the stable member URN', ($mette['commenter_member_urn'] ?? null) === 'urn:li:member:99887766');
ok('resolves the fsd_profile URN for messaging', ($mette['commenter_urn'] ?? null) === 'urn:li:fsd_profile:ACoAAABmette');
ok('resolves name across the reference', ($mette['commenter_name'] ?? '') === 'Mette Sørensen', $mette['commenter_name'] ?? '');
ok('resolves headline', str_contains($mette['commenter_headline'] ?? '', 'Novo Nordisk'));
ok('unwraps {text:} commentary', ($mette['comment_text'] ?? '') === 'GUIDE — vi kæmper med governance lige nu');
ok('converts millisecond timestamp', !empty($mette['commented_at']));
ok('captures network distance', ($mette['commenter_distance'] ?? null) === 'DISTANCE_2');
ok('assembles the profile image URL', str_contains($mette['commenter_image_url'] ?? '', 'mette_100.jpg'));
ok('counts resolved/unresolved', $result['resolved'] === 2 && $result['unresolved'] === 0, "{$result['resolved']}/{$result['unresolved']}");

section('the regression this replaced');
$old = $oldParser($graph);
$oldUsable = array_filter($old, static fn ($c) => $c['commenter_profile_url'] !== '');
ok('old parser resolved ZERO profile URLs here', count($oldUsable) === 0, (string)count($oldUsable));

// ---------------------------------------------------------------------------
section('legacy inline shape');

$legacy = ['elements' => [[
    'entityUrn' => 'urn:li:comment:(activity:7220,555)',
    'commentV2' => ['text' => 'Ja tak — GUIDE'],
    'commenter' => [
        'commenterProfileId' => 'anders-kjaer',
        'firstName' => 'Anders', 'lastName' => 'Kjær',
        'headline'  => 'Head of Digital @ Danske Bank',
        'urn'       => 'urn:li:member:5551212',
    ],
]]];

$legacyResult = CommentParser::parse($legacy);
$anders = $legacyResult['comments'][0] ?? [];

// commenterProfileId is overloaded: a slug on this shape, a URN on the dash shape.
ok('accepts commenterProfileId as a slug', ($anders['commenter_public_id'] ?? null) === 'anders-kjaer', var_export($anders['commenter_public_id'] ?? null, true));
ok('builds the profile URL', ($anders['commenter_profile_url'] ?? '') === 'https://www.linkedin.com/in/anders-kjaer');
ok('reads the name', ($anders['commenter_name'] ?? '') === 'Anders Kjær');
ok('keeps a URN for messaging', in_array('urn:li:member:5551212', [$anders['commenter_member_urn'] ?? null, $anders['commenter_urn'] ?? null], true));
ok('reads commentV2 text', ($anders['comment_text'] ?? '') === 'Ja tak — GUIDE');

section('a URN must never be mistaken for a slug');
$urnAsId = CommentParser::parse(['elements' => [[
    'entityUrn' => 'urn:li:comment:9',
    'commentary' => 'x',
    'commenter' => ['commenterProfileId' => 'urn:li:fsd_profile:ACoAAA'],
]]]);
ok('URN-shaped commenterProfileId is not used as a slug', ($urnAsId['comments'][0]['commenter_public_id'] ?? null) === null, var_export($urnAsId['comments'][0]['commenter_public_id'] ?? null, true));
ok('but is still kept as the messaging URN', ($urnAsId['comments'][0]['commenter_urn'] ?? null) === 'urn:li:fsd_profile:ACoAAA');

// ---------------------------------------------------------------------------
section('robustness — malformed input must never throw');

$hostile = [
    'empty payload'          => [],
    'included only'          => ['included' => [['entityUrn' => 'urn:li:fsd_profile:x', 'firstName' => 'A']]],
    'comment with no author' => ['elements' => [['entityUrn' => 'urn:li:comment:1', 'commentary' => 'hi']]],
    'null commenter'         => ['elements' => [['entityUrn' => 'urn:li:comment:2', 'commenter' => null]]],
    'dangling reference'     => ['data' => ['*elements' => ['urn:li:fsd_comment:missing']], 'included' => []],
    'scalars in included'    => ['included' => ['not-an-array', 42]],
    'deeply nested text'     => ['elements' => [['entityUrn' => 'urn:li:comment:3', 'commentary' => ['text' => ['text' => 'deep']]]]],
];
foreach ($hostile as $label => $payload) {
    try {
        $r = CommentParser::parse($payload);
        ok("no throw: {$label}", is_array($r['comments']));
    } catch (Throwable $e) {
        ok("no throw: {$label}", false, $e->getMessage());
    }
}

$deep = CommentParser::parse($hostile['deeply nested text']);
ok('flattens doubly-nested text', ($deep['comments'][0]['comment_text'] ?? '') === 'deep');

$noAuthor = CommentParser::parse($hostile['comment with no author']);
ok('counts an unresolvable author', $noAuthor['unresolved'] === 1, (string)$noAuthor['unresolved']);

// ---------------------------------------------------------------------------
section('dedupe');

$dupe = [
    'data' => ['*elements' => ['urn:li:fsd_comment:9']],
    'included' => [
        ['entityUrn' => 'urn:li:fsd_comment:9', '$type' => 'com.linkedin.voyager.dash.social.Comment',
         'commentary' => 'once', 'commenter' => ['*author' => 'urn:li:fsd_profile:p']],
        ['entityUrn' => 'urn:li:fsd_profile:p', 'publicIdentifier' => 'p', 'firstName' => 'P'],
    ],
];
ok('a comment reachable twice yields one row', count(CommentParser::parse($dupe)['comments']) === 1);
