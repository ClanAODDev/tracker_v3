<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CL\Slack\Payload\ChannelsArchivePayload;
use CL\Slack\Payload\ChannelsCreatePayload;
use CL\Slack\Payload\ChannelsInfoPayload;
use CL\Slack\Payload\ChannelsListPayload;
use CL\Slack\Transport\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlackChannelController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = new ApiClient(config('services.slack.token'));
        $this->middleware(['admin', 'auth']);
    }

    public function index()
    {
        $payload = new ChannelsListPayload();

        $response = $this->client->send($payload);
        $channels = collect($response->getChannels())->filter(function ($channel) {
            return ! $channel->isArchived();
        });

        if ($response->isOk()) {
            return view('admin.manage-slack-channels', compact('channels'));
        } else {
            return view('errors.500');
        }
    }

    public function create()
    {
        $payload = new ChannelsCreatePayload();
        $channelName = str_slug(request()->get('division') . "-" . request()->get('channel-name'));

        $payload->setName($channelName);

        $response = $this->client->send($payload);

        if ($response->isOk()) {
            $this->showToast("{$channelName} was created!");
            Log::info(auth()->user()->name
                . " created a slack channel - {$channelName} - "
                . Carbon::now());

            return redirect()->back();
        } else {
            return redirect()->back()->withErrors([
                'slack-error' => $response->getError(),
                'slack-error-detail' => $response->getErrorExplanation()
            ])->withInput();
        }
    }

    public function confirmArchive($channel)
    {
        $payload = new ChannelsInfoPayload();
        $payload->setChannelId($channel);
        $response = $this->client->send($payload);

        if ($response->isOk()) {
            return view('admin.confirm-archive', ['channel' => $response->getChannel()]);
        } else {
            return redirect()->back()->withErrors([
                'slack-error' => $response->getError(),
                'slack-error-detail' => $response->getErrorExplanation()
            ])->withInput();
        }
    }

    public function archive()
    {
        $payload = new ChannelsArchivePayload();
        $payload->setChannelId(request()->channel_id);
        $response = $this->client->send($payload);

        if ($response->isOk()) {
            $this->showToast("Channel was archived!");
            Log::info(auth()->user()->name
                . " archived a slack channel - "
                . request()->channel_id . " - " . Carbon::now());

            return redirect()->route('slack.index');
        } else {
            return redirect()->back()->withErrors([
                'slack-error' => $response->getError(),
                'slack-error-detail' => $response->getErrorExplanation()
            ])->withInput();
        }
    }
}
