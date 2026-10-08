<?php

declare(strict_types=1);

namespace Hampel\XenForo\Api\Tests;

use Hampel\XenForo\Api\Result\SiteInfo;
use PHPUnit\Framework\TestCase as BaseTestCase;

final class SiteInfoTest extends BaseTestCase
{
    /**
     * @return array{version_id: int, site_title: string, base_url: string, api_url: string, key: array{type: string, user_id: int, allow_all_scopes: bool, scopes: array<mixed>}}
     */
    private function response(): array
    {
        return [
            'version_id' => 2031270,
            'site_title' => 'Example Forum',
            'base_url' => 'https://forum.example.com',
            'api_url' => 'https://forum.example.com/api',
            'key' => [
                'type' => 'user',
                'user_id' => 4264,
                'allow_all_scopes' => false,
                // As a real forum sends it: a map of scope to true, not the list the
                // documentation describes. Captured from 2.3.12 with a scoped user key.
                'scopes' => ['user:read' => true, 'thread:read' => true],
            ],
        ];
    }

    public function test_it_reads_the_index_response(): void
    {
        $info = SiteInfo::fromArray($this->response());

        $this->assertSame('Example Forum', $info->siteTitle);
        $this->assertSame('user', $info->keyType);
        $this->assertSame(4264, $info->keyUserId);
        $this->assertFalse($info->isSuperUserKey());
    }

    /**
     * XenForo's version_id is abbccde, so 2031270 is 2.3.12 - the only way to tell whether
     * an endpoint added in a later release will be there.
     */
    public function test_it_decodes_the_version_id(): void
    {
        $this->assertSame('2.3.12', SiteInfo::fromArray($this->response())->version());
    }

    public function test_scopes_decide_what_the_key_may_do(): void
    {
        $info = SiteInfo::fromArray($this->response());

        $this->assertSame(['user:read', 'thread:read'], $info->scopes);
        $this->assertTrue($info->hasScope('user:read'));
        $this->assertFalse($info->hasScope('user:write'));
    }

    /**
     * The documentation promises a list of strings, and the specification generated from
     * it says the same. No XenForo line sends one, but the shape it promises is read too,
     * so a forum that one day keeps the promise still answers correctly.
     */
    public function test_the_documented_list_of_scopes_is_read_as_well(): void
    {
        $response = $this->response();
        $response['key']['scopes'] = ['user:read', 'thread:read'];

        $this->assertSame(['user:read', 'thread:read'], SiteInfo::fromArray($response)->scopes);
    }

    /**
     * A scope mapped to false is not granted; and a user key with no scopes answers an
     * empty JSON array rather than an empty object, which decodes to [] either way.
     */
    public function test_a_scope_mapped_to_false_is_not_held(): void
    {
        $response = $this->response();
        $response['key']['scopes'] = ['user:read' => true, 'user:write' => false];

        $info = SiteInfo::fromArray($response);

        $this->assertSame(['user:read'], $info->scopes);
        $this->assertFalse($info->hasScope('user:write'));
    }

    /**
     * A super-user key reports allow_all_scopes and an empty scope list, so reading the
     * list alone would say it can do nothing.
     */
    public function test_a_super_user_key_has_every_scope(): void
    {
        $response = $this->response();
        $response['key'] = ['type' => 'super', 'user_id' => null, 'allow_all_scopes' => true, 'scopes' => []];

        $info = SiteInfo::fromArray($response);

        $this->assertTrue($info->isSuperUserKey());
        $this->assertTrue($info->hasScope('anything:at:all'));
        $this->assertNull($info->keyUserId);
    }
}
