<?php

namespace Tests\Unit;

use GrahamCampbell\Markdown\Facades\Markdown;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarkdownConfigTest extends TestCase
{
    #[Test]
    public function it_drops_javascript_links(): void
    {
        $html = (string) Markdown::convertToHtml('[click](javascript:alert(1))');

        $this->assertStringNotContainsString('javascript:', $html);
    }

    #[Test]
    public function it_keeps_https_links(): void
    {
        $html = (string) Markdown::convertToHtml('[forums](https://clanaod.net/forums)');

        $this->assertStringContainsString('href="https://clanaod.net/forums"', $html);
    }
}
