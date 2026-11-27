<?php

namespace App\Support\Markdown;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;

class CalloutRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        Callout::assertInstanceOf($node);

        $title = $node->title !== null
            ? new HtmlElement('p', ['class' => 'callout-title'], Xml::escape($node->title))
            : '';

        return new HtmlElement(
            $node->type === CalloutType::Hero ? 'section' : 'aside',
            ['class' => "callout callout-{$node->type->value}"],
            [$title, new HtmlElement('div', ['class' => 'callout-body'], $childRenderer->renderNodes($node->children()))],
        );
    }
}
