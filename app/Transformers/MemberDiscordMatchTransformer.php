<?php

namespace App\Transformers;

class MemberDiscordMatchTransformer extends Transformer
{
    public function transform($item): array
    {
        return [
            'name'       => $item->name,
            'clan_id'    => $item->clan_id,
            'division'   => $item->division_id ? $item->division?->name : null,
            'url'        => route('member', $item->getUrlParams()),
            'isExMember' => $item->division_id === 0,
            'avatarUrl'  => $item->getDiscordAvatarUrl(),
        ];
    }
}
