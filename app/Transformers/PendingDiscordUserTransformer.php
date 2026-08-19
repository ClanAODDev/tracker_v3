<?php

namespace App\Transformers;

use App\Models\User;
use Illuminate\Support\Collection;

class PendingDiscordUserTransformer extends Transformer
{
    public function transform($item): array
    {
        return [
            'id'                   => $item->id,
            'discord_username'     => $item->discord_username,
            'forum_name'           => $item->name,
            'discord_id'           => $item->discord_id,
            'email'                => $item->email,
            'obfuscated_email'     => $this->obfuscateEmail($item->email),
            'avatar_url'           => $item->getDiscordAvatarUrl(),
            'created_at'           => $item->created_at->diffForHumans(),
            'application'          => $this->transformApplication($item),
            'application_division' => $item->divisionApplication?->division?->name,
        ];
    }

    private function transformApplication(User $user): ?Collection
    {
        if (! $user->divisionApplication) {
            return null;
        }

        return collect($user->divisionApplication->responses)->map(fn ($response) => [
            'label' => $response['label'] ?? 'Unknown',
            'value' => is_array($response['value'] ?? null) ? implode(', ', $response['value']) : ($response['value'] ?? '—'),
        ])->values();
    }

    private function obfuscateEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);

        return '***' . mb_substr($local, -2) . '@' . $domain;
    }
}
