<?php

namespace Tests\Unit\Support;

use GrahamCampbell\Markdown\Facades\Markdown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarkdownCalloutTest extends TestCase
{
    private function render(string $markdown): string
    {
        return (string) Markdown::convertToHtml($markdown);
    }

    #[Test]
    #[DataProvider('labelledTypes')]
    public function a_typed_quote_becomes_a_labelled_callout(string $marker, string $class, string $label): void
    {
        $html = $this->render("> {$marker}\n> Keep your handles up to date.");

        $this->assertStringContainsString("<aside class=\"callout {$class}\"><p class=\"callout-title\">{$label}</p>", $html);
        $this->assertStringContainsString('<p>Keep your handles up to date.</p>', $html);
        $this->assertStringNotContainsString('[!', $html);
        $this->assertStringNotContainsString('<blockquote', $html);
    }

    public static function labelledTypes(): array
    {
        return [
            ['[!NOTE]', 'callout-note', 'Note'],
            ['[!tip]', 'callout-tip', 'Tip'],
            ['[!IMPORTANT]', 'callout-important', 'Important'],
            ['[!WARNING]', 'callout-warning', 'Warning'],
            ['[!CAUTION]', 'callout-caution', 'Caution'],
        ];
    }

    #[Test]
    public function text_after_the_type_replaces_the_label(): void
    {
        $html = $this->render("> [!TIP] Before you start\n> Back up the file.");

        $this->assertStringContainsString('<p class="callout-title">Before you start</p>', $html);
    }

    #[Test]
    public function the_label_is_escaped(): void
    {
        $html = $this->render("> [!TIP] <script>alert(1)</script>\n> Body.");

        $this->assertStringNotContainsString('<script>', $html);
    }

    #[Test]
    public function a_hero_callout_has_no_label_and_keeps_its_blocks(): void
    {
        $html = $this->render("> [!HERO]\n> # What's new\n> Welcome to the **November** release.");

        $this->assertStringStartsWith('<section class="callout callout-hero"><div class="callout-body"><h1', $html);
        $this->assertStringNotContainsString('callout-title', $html);
        $this->assertStringContainsString('<strong>November</strong>', $html);
    }

    #[Test]
    public function plain_quotes_and_unknown_types_stay_quotes(): void
    {
        $this->assertStringContainsString('<blockquote', $this->render('> Just a quote.'));
        $this->assertStringContainsString('[!BOGUS]', $this->render("> [!BOGUS]\n> Still a quote."));
        $this->assertStringNotContainsString('callout', $this->render("> Mentioning [!TIP] mid-line.\n> More."));
    }
}
