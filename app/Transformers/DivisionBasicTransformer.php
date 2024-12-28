<?php

namespace App\Transformers;

class DivisionBasicTransformer extends Transformer
{
    public function __construct(private readonly MemberBasicTransformer $memberTransformer) {}

    public function transform($item): array
    {
        $data = [
            'name' => $item->name,
            'slug' => $item->slug,
            'abbreviation' => $item->abbreviation,
            'description' => $item->description,
            'forum_app_id' => $item->forum_app_id,
            'members_count' => $item->members_count,
            'show_on_site' => $item->show_on_site,
            'officer_channel' => $item->settings()->get('officer_channel'),
            'icon' => $item->getLogoPath(),
            'leadership' => $this->memberTransformer->transformCollection(
                $item->leaders()->get()->all()
            ),
        ];

        if (request('include_site')) {
            $data['site_content'] = $item->site_content;
        }

        return $data;
    }
}
