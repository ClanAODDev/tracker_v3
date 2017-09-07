<?php

namespace App\Http\Controllers\Slack;

use App\Http\Requests\Slack\ArchiveChannel;
use App\Http\Requests\Slack\CreateChannel;
use App\Http\Requests\Slack\UnarchiveChannel;
use wrapi\slack\slack;

class SlackChannelController extends SlackController
{

    private $client;

    public function __construct(slack $client)
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

        $division = auth()->user()->member->division;

        $response = $this->client->channels->list();

        $channels = $response['channels'];

        if ( ! auth()->user()->isRole('admin')) {
            $channels = collect($response['channels'])->filter(function ($item) use ($division) {
                return str_contains($item['name'], $division->abbreviation . '-')
                    || str_contains($item['name'], str_slug($division->name));
            });
        }

        if ($response['ok']) {
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

        if ($response['ok']) {
            $this->showToast("{$channelName} was created!");
            $this->logSlackAction(" created a slack channel - {$channelName}");

            return redirect()->back();
        } else {
            return $this->getSlackErrorResponse($response);
        }
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

        $response = $this->client->channels->info(['channel' => $channel]);

        if ($response['ok']) {
            return view('slack.confirm-archive', ['channel' => $response['channel']]);
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

        if ($response['ok']) {
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

        if ($response['ok']) {
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
        if ($response['ok']) {
            return $response;
        } else {
            return $response->getError();
        }
        */
    }
}
