<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests;

use Eduardokum\LaravelMailAutoEmbed\Tests\Traits\InteractsWithMessage;

/**
 * Tests some scenarios, like HTML5 mails and mail with "invalid" HTML that mail clients
 * like.
 */
class FormatTest extends TestCase
{
    use InteractsWithMessage;

    private const HANDLE_CONFIG = [
        'enabled' => true,
        'method' => 'attachment',
    ];

    /**
     * @test
     */
    public function testValidHtml5Message()
    {
        $message = $this->handleBeforeSendPerformedEvent('formats/html5-valid.html', self::HANDLE_CONFIG);

        $this->assertEmailImageTags([
            'url' => 'cid:',
            'entity' => 'cid:',
        ], $message);
    }

    /**
     * @test
     */
    public function testUserGeneratedHtml5Message()
    {
        $message = $this->handleBeforeSendPerformedEvent('formats/html5-user-generated.html', self::HANDLE_CONFIG);

        $this->assertEmailImageTags([
            'url' => 'cid:',
            'entity' => 'cid:',
        ], $message);
    }
}
