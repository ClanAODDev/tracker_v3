<?php

namespace App\Http\Requests\Slack;

use Illuminate\Foundation\Http\FormRequest;

class UnarchiveChannel extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }

    public function persist($client, $channel)
    {
        return $client->channels->unarchive([
            'channel' => $channel,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manageSlack', auth()->user());
    }
}
