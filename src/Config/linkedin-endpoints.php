<?php

/**
 * Seed catalog for linkedin_endpoints.
 *
 * The DB row always wins at runtime (LinkedInEndpoint::get). This file is what
 * a fresh install / a broken-row reset loads. queryIds rot; harvest them from
 * a live session and UPDATE the row rather than deploying PHP.
 *
 * Keys are the logical names ActionExecutor / LinkedInService use.
 */
return [
    'identity.me' => [
        'transport' => 'rest',
        'method'    => 'GET',
        'path'      => '/voyager/api/identity/profiles/me',
        'shape'     => 'profile',
        'risk_level'=> 'low',
        'is_write'  => 0,
    ],
    'identity.profile' => [
        'transport' => 'rest',
        'method'    => 'GET',
        'path'      => '/voyager/api/identity/profiles/{publicId}',
        'shape'     => 'profile',
        'risk_level'=> 'low',
        'is_write'  => 0,
    ],
    'identity.profile_view' => [
        'transport' => 'rest',
        'method'    => 'POST',
        'path'      => '/voyager/api/identity/profiles/{publicId}/profileView',
        'shape'     => 'generic',
        'risk_level'=> 'medium',
        'is_write'  => 1,
    ],
    'search.people' => [
        'transport' => 'rest',
        'method'    => 'GET',
        'path'      => '/voyager/api/search/dash/clusters',
        'shape'     => 'search',
        'risk_level'=> 'medium',
        'is_write'  => 0,
    ],
    'feed.comments' => [
        'transport' => 'rest',
        'method'    => 'GET',
        'path'      => '/voyager/api/feed/dash/comments',
        'shape'     => 'comments',
        'risk_level'=> 'low',
        'is_write'  => 0,
    ],
    'feed.comment.create' => [
        'transport' => 'rest',
        'method'    => 'POST',
        'path'      => '/voyager/api/feed/dash/comments',
        'shape'     => 'generic',
        'risk_level'=> 'high',
        'is_write'  => 1,
    ],
    'social.react' => [
        'transport' => 'rest',
        'method'    => 'POST',
        'path'      => '/voyager/api/voyagerSocialDashReactions',
        'shape'     => 'generic',
        'risk_level'=> 'medium',
        'is_write'  => 1,
    ],
    'identity.posts' => [
        'transport' => 'rest',
        'method'    => 'GET',
        'path'      => '/voyager/api/identity/profileUpdatesV2',
        'shape'     => 'posts',
        'risk_level'=> 'low',
        'is_write'  => 0,
        'notes'     => 'Own or other member share feed. Falls back to identity/dash profileUpdates.',
        'fallback_path' => '/voyager/api/identity/dash/profiles/{profileUrn}/profileUpdates',
    ],
    'messaging.create' => [
        'transport' => 'rest',
        'method'    => 'POST',
        'path'      => '/voyager/api/voyagerMessagingDashMessengerMessages?action=createMessage',
        'shape'     => 'generic',
        'risk_level'=> 'high',
        'is_write'  => 1,
    ],
    'relationships.invite' => [
        'transport' => 'rest',
        'method'    => 'POST',
        'path'      => '/voyager/api/voyagerRelationshipsDashMemberRelationships?action=verifyQuotaAndCreate',
        'shape'     => 'generic',
        'risk_level'=> 'high',
        'is_write'  => 1,
    ],
];
