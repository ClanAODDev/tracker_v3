<?php

namespace App\Http\Controllers\Slack;

use App\Http\Controllers\Controller;
use App\Http\Requests\Slack\ArchiveChannel;
use App\Http\Requests\Slack\CreateChannel;
use App\Http\Requests\Slack\UnarchiveChannel;
use Carbon\Carbon;
use CL\Slack\Payload\ChannelsInfoPayload;
use CL\Slack\Payload\ChannelsListPayload;
use CL\Slack\Transport\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlackChannelController extends Controller
{

    private $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
        $this->middleware('auth');
    }


    /**
     * List all slack channels
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('manageSlack', auth()->user());
        $payload = new ChannelsListPayload();
        $division = auth()->user()->member->division;

        $response = $this->client->send($payload);
        $channels = collect($response->getChannels())->groupBy(function ($item, $key) {
            return $item->isArchived();
        })->flatten();

        if (! auth()->user()->isRole('admin')) {
            $channels = collect($channels)->filter(function ($item, $key) use ($division) {
                return str_contains($item->getName(), $division->abbreviation . '-')
                    || str_contains($item->getName(), str_slug($division->name));
            })->flatten();
        }

        if ($response->isOk()) {
            return view('slack.channels', compact('channels', 'division'));
        } else {
            return view('errors.500');
        }
    }

    /**
     * Create a slack channel
     *
     * @param CreateChannel $form
     * @return SlackChannelController|\Illuminate\Http\RedirectResponse
     */
    public function store(CreateChannel $form)
    {
        $response = $form->persist($this->client);
        $channelName = "{$form['division']}-{$form['channel-name']}";

        if ($response->isOk()) {
            $this->showToast("{$channelName} was created!");
            $this->logSlackAction(" created a slack channel - {$channelName}");

            return redirect()->back();
        } else {
            return $this->getSlackErrorResponse($response);
        }
    }

    /**
     * @param $message
     */
    private function logSlackAction($message)
    {
        Log::info(auth()->user()->name . $message . Carbon::now());
    }

    /**
     * @param $response
     * @return SlackChannelController|\Illuminate\Http\RedirectResponse
     */
    private function getSlackErrorResponse($response)
    {
        return redirect()->back()->withErrors([
            'slack-error' => $response->getError(),
            'slack-error-detail' => $response->getErrorExplanation()
        ])->withInput();
    }

    /**
     * Confirm archiving request
     *
     * @param $channel
     * @return SlackChannelController|\Illuminate\Http\RedirectResponse
     */
    public function confirmArchive($channel)
    {
        $this->authorize('manageSlack', auth()->user());

        $payload = new ChannelsInfoPayload();
        $payload->setChannelId($channel);
        $response = $this->client->send($payload);

        if ($response->isOk()) {
            return view('slack.confirm-archive', ['channel' => $response->getChannel()]);
        } else {
            return $this->getSlackErrorResponse($response);
        }
    }

    /**
     * Archive a slack channel
     *
     * @param ArchiveChannel $form
     * @return SlackChannelController|\Illuminate\Http\RedirectResponse
     */
    public function archive(ArchiveChannel $form)
    {
        $response = $form->persist($this->client);

        if ($response->isOk()) {
            $this->showToast("Channel was archived!");
            $this->logSlackAction("archived a slack channel - " . request()->channel_id);

            return redirect()->route('slack.channel-index');
        } else {
            return $this->getSlackErrorResponse($response);
        }
    }

    /**
     * Unarchive a slack channel
     *
     * @param $channel
     * @param UnarchiveChannel $form
     * @return SlackChannelController|\Illuminate\Http\RedirectResponse
     */
    public function unarchive($channel, UnarchiveChannel $form)
    {
        $response = $form->persist($this->client, $channel);

        if ($response->isOk()) {
            $this->showToast("Channel was unarchived!");
            $this->logSlackAction("unarchived a slack channel - " . request()->channel_id);

            return redirect()->route('slack.channel-index');
        } else {
            return $this->getSlackErrorResponse($response);
        }
    }

    public function sendMessage()
    {
        // for later
        /*
        $payload = new ChatPostMessagePayload();
        $payload->setChannel('msgt_up');
        $payload->setText('this is a test... this is only a test');
        $payload->setUsername('ClanAOD');
        $payload->setIconEmoji('aodbrand');
        $response = $this->client->send($payload);
        if ($response->isOk()) {
            return $response;
        } else {
            return $response->getError();
        }
        */
    }
}
