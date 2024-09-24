<?php

namespace App\Transformers;

class DivisionBasicTransformer extends Transformer
{
    public function __construct(private readonly MemberBasicTransformer $memberTransformer) {}

    public function transform($item): array
    {
        return [
            'name' => $item->name,
            'slug' => $item->slug,
            'abbreviation' => $item->abbreviation,
            'description' => $item->description,
            'forum_app_id' => $item->forum_app_id,
            'members_count' => $item->members_count,
            'officer_channel' => $item->settings()->get('officer_channel'),
            'icon' => $item->getLogoPath(),
            'leadership' => $this->memberTransformer->transformCollection(
                $item->leaders()->get()->all()
            ),
        ];
    }
}
