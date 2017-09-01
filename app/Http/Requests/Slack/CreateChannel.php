<?php

namespace App\Http\Requests\Slack;

use CL\Slack\Payload\ChannelsCreatePayload;
use CL\Slack\Transport\ApiClient;
use Illuminate\Foundation\Http\FormRequest;

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

    public function persist(ApiClient $client)
    {
        $payload = new ChannelsCreatePayload();
        $channelName = str_slug(request()->get('division') . "-" . request()->get('channel-name'));
        $payload->setName($channelName);

        return $client->send($payload);
    }
}
