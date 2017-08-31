<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CL\Slack\Payload\ChannelsArchivePayload;
use CL\Slack\Payload\ChannelsCreatePayload;
use CL\Slack\Payload\ChannelsInfoPayload;
use CL\Slack\Payload\ChannelsListPayload;
use CL\Slack\Payload\ChannelsUnarchivePayload;
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

    /**
     * List all slack channels
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $payload = new ChannelsListPayload();

        $response = $this->client->send($payload);
        $channels = collect($response->getChannels())->groupBy(function ($item, $key) {
            return $item->isArchived();
        })->flatten();

        if ($response->isOk()) {
            return view('admin.manage-slack-channels', compact('channels'));
        } else {
            return view('errors.500');
        }
    }

    /**
     * Create a slack channel
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Confirm archiving request
     *
     * @param $channel
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

    /**
     * Archive a slack channel
     *
     * @return $this|\Illuminate\Http\RedirectResponse
     */
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

    /**
     * Unarchive a slack channel
     *
     * @param $channel
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function unarchive($channel)
    {
        $payload = new ChannelsUnarchivePayload();
        $payload->setChannelId($channel);
        $response = $this->client->send($payload);

        if ($response->isOk()) {
            $this->showToast("Channel was unarchived!");
            Log::info(auth()->user()->name
                . " unarchived a slack channel - "
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
