<?php

namespace App\Services\Outreach;

final class SuggestAutomationSettings
{
    /**
     * @return array{
     *   name:string, keywords:list<string>, auto_reply_template:string,
     *   non_first_degree_reply_template:string, dm_template:string,
     *   follow_up_template:string
     * }|null
     */
    public static function fromPost(string $postText, ?string $leadMagnetTitle = null): ?array
    {
        if (!LlmClient::isConfigured()) {
            return self::heuristic($postText);
        }

        $system = <<<'PROMPT'
You draft LinkedIn comment-to-DM automation settings. Return JSON only with keys:
name (short automation name),
keywords (3-6 trigger words people would comment, lowercase unless they are acronyms),
auto_reply_template (public comment reply, include {{fullNameMention}} once),
non_first_degree_reply_template (reply for people we are not connected to yet),
dm_template (DM that delivers the resource, use {{firstName}} and {{lead_magnet_url}}),
follow_up_template (short follow-up if they do not reply, use {{firstName}}).
Keep copy warm, short, not salesy. English.
PROMPT;

        $user = "Post:\n" . mb_substr($postText, 0, 4000);
        if ($leadMagnetTitle) {
            $user .= "\n\nLead magnet title: " . $leadMagnetTitle;
        }

        $json = LlmClient::chatJson($system, $user);
        if ($json === null) {
            return self::heuristic($postText);
        }

        $keywords = KeywordMatcher::normalize($json['keywords'] ?? []);
        if ($keywords === []) {
            $keywords = ['interested'];
        }

        return [
            'name' => (string) ($json['name'] ?? 'Post automation'),
            'keywords' => $keywords,
            'auto_reply_template' => (string) ($json['auto_reply_template'] ?? 'Sent to your DMs — check your inbox.'),
            'non_first_degree_reply_template' => (string) ($json['non_first_degree_reply_template'] ?? 'Sending a connection request so I can DM you the resource.'),
            'dm_template' => (string) ($json['dm_template'] ?? 'Hi {{firstName}}, here you go: {{lead_magnet_url}}'),
            'follow_up_template' => (string) ($json['follow_up_template'] ?? 'Hi {{firstName}}, just checking you got the link: {{lead_magnet_url}}'),
        ];
    }

    /** @return array<string,mixed> */
    public static function heuristic(string $postText): array
    {
        $keywords = [];
        if (preg_match_all('/[“"\']([A-Za-z][A-Za-z0-9_-]{1,24})[”"\']/', $postText, $m)) {
            $keywords = array_slice(array_unique($m[1]), 0, 5);
        }
        if ($keywords === []) {
            if (preg_match('/\b(GUIDE|PLAYBOOK|TEMPLATE|CHECKLIST|PDF|INTERESTED|SEND|YES)\b/i', $postText, $m)) {
                $keywords = [strtoupper($m[1])];
            } else {
                $keywords = ['interested', 'send', 'guide'];
            }
        }

        return [
            'name' => 'Comment-to-DM',
            'keywords' => $keywords,
            'auto_reply_template' => '{{fullNameMention}} sent to your DMs — check your inbox.',
            'non_first_degree_reply_template' => '{{fullNameMention}} sending a connection request so I can DM you the resource.',
            'dm_template' => "Hi {{firstName}}, thanks for commenting — here is the resource:\n{{lead_magnet_url}}",
            'follow_up_template' => 'Hi {{firstName}}, just making sure this reached you: {{lead_magnet_url}}',
        ];
    }
}
