<?php

namespace App\Http\Requests\Slack;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use wrapi\slack\slack;

class CreateChannel extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manageSlack', auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'channel-name' => 'required|max:21'
        ];
    }

    public function persist(slack $client)
    {
        $channelName = Str::slug(request()->get('division') . "-" . request()->get('channel-name'));

        return $client->channels->create([
            'name' => $channelName
        ]);
    }
}
