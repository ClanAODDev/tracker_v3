<?php

namespace App\Support\Markdown;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;

class CalloutProcessor
{
    private const string MARKER = '/^\[!(?<type>[a-z]+)\][ \t]*(?<title>.*)$/i';

    public function __invoke(DocumentParsedEvent $event): void
    {
        $quotes = [];

        foreach ($event->getDocument()->iterator() as $node) {
            if ($node instanceof BlockQuote) {
                $quotes[] = $node;
            }
        }

        foreach ($quotes as $quote) {
            $this->convert($quote);
        }
    }

    private function convert(BlockQuote $quote): void
    {
        $paragraph = $quote->firstChild();

        if (! $paragraph instanceof Paragraph) {
            return;
        }

        [$line, $markerNodes] = $this->firstLine($paragraph);

        if (! preg_match(self::MARKER, $line, $match)) {
            return;
        }

        $type = CalloutType::tryFrom(strtolower($match['type']));

        if ($type === null) {
            return;
        }

        foreach ($markerNodes as $node) {
            $node->detach();
        }

        if ($paragraph->firstChild() === null) {
            $paragraph->detach();
        }

        $title   = trim($match['title']);
        $callout = new Callout($type, $title !== '' ? $title : $type->defaultTitle());

        while ($child = $quote->firstChild()) {
            $callout->appendChild($child);
        }

        $quote->replaceWith($callout);
    }

    /**
     * @return array{0: string, 1: list<Node>}
     */
    private function firstLine(Paragraph $paragraph): array
    {
        $line  = '';
        $nodes = [];

        for ($node = $paragraph->firstChild(); $node !== null; $node = $node->next()) {
            $nodes[] = $node;

            if ($node instanceof Newline) {
                break;
            }

            if (! $node instanceof Text) {
                return ['', []];
            }

            $line .= $node->getLiteral();
        }

        return [$line, $nodes];
    }
}
