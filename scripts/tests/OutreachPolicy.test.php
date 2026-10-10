<?php

require_once dirname(__DIR__, 2) . '/src/Services/Outreach/ActionPolicy.php';
require_once dirname(__DIR__, 2) . '/src/Services/Outreach/KeywordMatcher.php';
require_once dirname(__DIR__, 2) . '/src/Services/Outreach/TemplateRenderer.php';
require_once dirname(__DIR__, 2) . '/src/Services/LinkedIn/RecentPostsParser.php';

use App\Services\LinkedIn\RecentPostsParser;
use App\Services\Outreach\ActionPolicy;
use App\Services\Outreach\KeywordMatcher;
use App\Services\Outreach\TemplateRenderer;

section('KeywordMatcher');

ok('splits comma list', KeywordMatcher::normalize('GUIDE, interested, send') === ['GUIDE', 'interested', 'send']);
ok('drops empty tokens', KeywordMatcher::normalize('GUIDE,,  ,yes') === ['GUIDE', 'yes']);
ok('empty string matches nothing', KeywordMatcher::normalize('') === []);
ok('does not treat blank as a match-all', KeywordMatcher::firstMatch('hello', '') === null);
ok('matches case-insensitively', KeywordMatcher::firstMatch('Please send the GUIDE', ['guide']) === 'guide');
ok('json array string', KeywordMatcher::normalize('["a","b"]') === ['a', 'b']);

section('TemplateRenderer');

$rendered = TemplateRenderer::render(
    'Hi {{firstName}} / {{first_name}} {{fullNameMention}} {{lead_magnet_url}}',
    ['first_name' => 'Ada', 'last_name' => 'Lovelace', 'lead_magnet_url' => 'https://x.test/lp']
);
ok('firstName + first_name + mention + url', $rendered === 'Hi Ada / Ada @Ada Lovelace https://x.test/lp');

$mentions = TemplateRenderer::mentionOffsets('@Ada Lovelace hi', [
    'full_name' => 'Ada Lovelace',
    'commenter_urn' => 'urn:li:fsd_profile:ACoAAA',
]);
ok('mention offset start', ($mentions[0]['start'] ?? null) === 0);
ok('mention offset length', ($mentions[0]['length'] ?? null) === mb_strlen('@Ada Lovelace'));
ok('no mention without urn', TemplateRenderer::mentionOffsets('@Ada', ['first_name' => 'Ada']) === []);

section('ActionPolicy');

$now = new DateTimeImmutable('2026-08-17 10:00:00', new DateTimeZone('UTC'));
$weekday = $now->setTimezone(new DateTimeZone('UTC'));

$account = [
    'status' => 'active',
    'emergency_stop' => 0,
    'daily_message_limit' => 50,
    'safety_mode' => 'auto',
    'respect_off_hours' => 0,
    'timezone' => 'UTC',
];
$limit = ['limit_value' => 50, 'used' => 10, 'is_paused' => 0, 'is_readonly' => 0, 'approval_mode' => 'auto'];

ok('healthy write allowed', ActionPolicy::evaluate($account, 'dm', 'cron', false, $limit, $now) === null);
ok('expired cookie refused', ActionPolicy::evaluate(['status' => 'cookie_expired'] + $account, 'dm', 'cron', false, $limit, $now) === 'account_unhealthy');
ok('emergency stop', ActionPolicy::evaluate(['emergency_stop' => 1] + $account, 'comment', 'cron', false, $limit, $now) === 'emergency_stop');
ok('dm cap 0 is emergency stop', ActionPolicy::evaluate(['daily_message_limit' => 0] + $account, 'dm', 'cron', false, $limit, $now) === 'emergency_stop');
ok('dm cap 0 does not block likes', ActionPolicy::evaluate(['daily_message_limit' => 0] + $account, 'reaction', 'cron', false, $limit, $now) === null);
ok('do not engage', ActionPolicy::evaluate($account, 'dm', 'cron', true, $limit, $now) === 'do_not_engage');
ok('channel paused', ActionPolicy::evaluate($account, 'dm', 'cron', false, ['is_paused' => 1] + $limit, $now) === 'channel_paused');
ok('channel stopped at 0', ActionPolicy::evaluate($account, 'dm', 'cron', false, ['limit_value' => 0] + $limit, $now) === 'channel_stopped');
ok('limit reached', ActionPolicy::evaluate($account, 'dm', 'cron', false, ['used' => 50] + $limit, $now) === 'limit_reached');
ok('cooldown', ActionPolicy::evaluate($account, 'dm', 'cron', false, ['next_action_at' => '2099-01-01 00:00:00'] + $limit, $now) === 'cooldown');

$offHoursAccount = array_merge($account, [
    'respect_off_hours' => 1,
    'working_hours_json' => json_encode(['mon' => [9, 17], 'tue' => [9, 17], 'wed' => [9, 17], 'thu' => [9, 17], 'fri' => [9, 17], 'sat' => null, 'sun' => null]),
]);
$sunday = new DateTimeImmutable('2026-08-16 12:00:00', new DateTimeZone('UTC')); // Sunday
ok('sunday is off hours', ActionPolicy::evaluate($offHoursAccount, 'dm', 'cron', false, $limit, $sunday) === 'off_hours');
ok('weekday noon is on hours', ActionPolicy::evaluate($offHoursAccount, 'dm', 'cron', false, $limit, $now) === null);
ok('no schedule configured is 24/7', ActionPolicy::evaluate(array_merge($account, ['respect_off_hours' => 1, 'working_hours_json' => null]), 'dm', 'cron', false, $limit, $sunday) === null);

ok('cron ignores queue safety', ActionPolicy::evaluate(['safety_mode' => 'queue'] + $account, 'dm', 'cron', false, $limit, $now) === null);
ok('mcp queue safety', ActionPolicy::evaluate(['safety_mode' => 'queue'] + $account, 'dm', 'mcp', false, $limit, $now) === 'needs_approval');
ok('read_only blocks mcp', ActionPolicy::evaluate(['safety_mode' => 'read_only'] + $account, 'dm', 'mcp', false, $limit, $now) === 'read_only');
ok('read_only allows cron', ActionPolicy::evaluate(['safety_mode' => 'read_only'] + $account, 'dm', 'cron', false, $limit, $now) === null);

section('RecentPostsParser');

$fixture = [
    'data' => ['*elements' => ['urn:li:activity:111']],
    'included' => [
        [
            'entityUrn' => 'urn:li:activity:111',
            'commentary' => ['text' => ['text' => 'Comment GUIDE for the pdf']],
            'createdTime' => 1700000000000,
            '*socialDetail' => 'urn:li:fs_socialDetail:111',
        ],
        [
            'entityUrn' => 'urn:li:fs_socialDetail:111',
            'totalSocialActivityCounts' => [
                'numComments' => 12,
                'numLikes' => 40,
                'numShares' => 3,
            ],
        ],
    ],
];
$posts = RecentPostsParser::parse($fixture);
ok('parses one post', count($posts) === 1);
ok('urn', ($posts[0]['post_urn'] ?? '') === 'urn:li:activity:111');
ok('commentary', ($posts[0]['commentary'] ?? '') === 'Comment GUIDE for the pdf');
ok('comments', ($posts[0]['comments'] ?? 0) === 12);
ok('reactions', ($posts[0]['reactions'] ?? 0) === 40);
ok('share url built', str_contains($posts[0]['share_url'] ?? '', 'urn:li:activity:111'));
