<?php

namespace App\Http\Requests;

use App\Services\AODBotService;
use Illuminate\Foundation\Http\FormRequest;

class SyncDiscordAvatar extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->user()->member;

        return $member !== null && $member->discord_id !== null;
    }

    public function rules(): array
    {
        return [];
    }

    public function persist(): void
    {
        $member = $this->user()->member;
        $hash   = app(AODBotService::class)->getMemberAvatar($member->discord_id);

        $member->timestamps = false;
        $member->update(['discord_avatar' => $hash]);
    }
}
