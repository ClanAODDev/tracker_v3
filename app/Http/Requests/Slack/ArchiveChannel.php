<?php

namespace App\Http\Requests\Slack;

use CL\Slack\Payload\ChannelsArchivePayload;
use CL\Slack\Transport\ApiClient;
use Illuminate\Foundation\Http\FormRequest;

class ArchiveChannel extends FormRequest
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
            //
        ];
    }

    public function persist(ApiClient $client)
    {
        $payload = new ChannelsArchivePayload();
        $payload->setChannelId(request()->channel_id);
        return $client->send($payload);
    }
}
