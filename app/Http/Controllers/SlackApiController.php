<?php

namespace App\Http\Controllers;


use CL\Slack\Payload\ChannelsArchivePayload;
use CL\Slack\Payload\ChannelsCreatePayload;
use CL\Slack\Payload\ChannelsListPayload;
use CL\Slack\Payload\ChatPostMessagePayload;
use CL\Slack\Transport\ApiClient;

class SlackApiController extends Controller
{
    public function __construct()
    {
        $this->client = new ApiClient(config('services.slack.token'));
    }

    public function index()
    {


    }

    public function archiveChannel($channel)
    {

        $payload = new ChannelsArchivePayload();
        $payload->setChannelId($channel);
        $response = $this->apiClient->send($payload);

        if ($response->isOk()) {
        } else {
            echo $response->getError();
            echo $response->getErrorExplanation();
        }
    }



}
