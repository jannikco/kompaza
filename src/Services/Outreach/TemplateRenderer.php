<?php

namespace App\Services\Outreach;

/**
 * Personalizes comment-reply and DM templates.
 *
 * Leadshark-compatible names ({{firstName}}) and the older ConnectPilot
 * names ({{first_name}}) both work. @mention tokens expand to "@Name" in
 * the text; the caller can also read mentionOffsets() for a Voyager
 * attributed-text payload when a profile URN is present.
 */
final class TemplateRenderer
{
    /**
     * @param array{
     *   first_name?:string, last_name?:string, full_name?:string,
     *   linkedin_username?:string, lead_magnet_url?:string,
     *   commenter_urn?:?string
     * } $vars
     */
    public static function render(string $template, array $vars): string
    {
        $first = trim((string) ($vars['first_name'] ?? ''));
        $last  = trim((string) ($vars['last_name'] ?? ''));
        $full  = trim((string) ($vars['full_name'] ?? trim($first . ' ' . $last)));
        if ($full === '') {
            $full = $first;
        }

        $map = [
            '{{first_name}}'         => $first,
            '{{last_name}}'          => $last,
            '{{full_name}}'          => $full,
            '{{firstName}}'          => $first,
            '{{lastName}}'           => $last,
            '{{fullName}}'           => $full,
            '{{linkedinUsername}}'   => (string) ($vars['linkedin_username'] ?? ''),
            '{{linkedin_username}}'  => (string) ($vars['linkedin_username'] ?? ''),
            '{{lead_magnet_url}}'    => (string) ($vars['lead_magnet_url'] ?? ''),
            '{{firstNameMention}}'   => $first !== '' ? '@' . $first : '',
            '{{fullNameMention}}'    => $full !== '' ? '@' . $full : '',
            '{{first_name_mention}}' => $first !== '' ? '@' . $first : '',
            '{{full_name_mention}}'  => $full !== '' ? '@' . $full : '',
        ];

        return strtr($template, $map);
    }

    /**
     * Voyager attributed-text mention spans, if the rendered text contains @Name
     * and we know the commenter's profile URN.
     *
     * @return list<array{start:int,length:int,urn:string}>
     */
    public static function mentionOffsets(string $rendered, array $vars): array
    {
        $urn = $vars['commenter_urn'] ?? null;
        if (!is_string($urn) || $urn === '' || !str_contains($urn, 'urn:li:')) {
            return [];
        }

        $first = trim((string) ($vars['first_name'] ?? ''));
        $full  = trim((string) ($vars['full_name'] ?? ''));
        $candidates = array_values(array_filter([
            $full !== '' ? '@' . $full : null,
            $first !== '' ? '@' . $first : null,
        ]));

        $out = [];
        foreach ($candidates as $needle) {
            $pos = mb_strpos($rendered, $needle);
            if ($pos === false) {
                continue;
            }
            $out[] = [
                'start'  => $pos,
                'length' => mb_strlen($needle),
                'urn'    => $urn,
            ];
            break;
        }
        return $out;
    }
}
