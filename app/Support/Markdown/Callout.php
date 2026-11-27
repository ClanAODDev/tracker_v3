<?php

namespace App\Support\Markdown;

use League\CommonMark\Node\Block\AbstractBlock;

class Callout extends AbstractBlock
{
    public function __construct(
        public readonly CalloutType $type,
        public readonly ?string $title = null,
    ) {
        parent::__construct();
    }
}
