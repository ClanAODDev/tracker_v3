<?php

namespace App\Http\Requests\Slack;

use Illuminate\Foundation\Http\FormRequest;
use wrapi\slack\slack;

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

    public function persist(slack $client)
    {
        return $client->channels->archive([
            'channel' => $this->channel_id
        ]);
    }
}
