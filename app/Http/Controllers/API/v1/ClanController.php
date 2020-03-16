<?php

namespace App\Http\Controllers\API\v1;

use App\Services\AOD;
use DateTimeInterface;
use Google_Client;
use Google_Service_Calendar_Event;
use Illuminate\Http\JsonResponse;

class ClanController extends ApiController
{

    /**
     * ClanController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return JsonResponse
     */
    public function teamspeakPopulationCount()
    {
        $data = AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_ts_population_json&');

        return $this->respond([
            'data' => $data
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function discordPopulationCount()
    {
        $data = AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_discord_population_json&');

        return $this->respond([
            'data' => $data
        ]);
    }

    public function eventStream()
    {
        $client = new Google_Client();
        $client->setApplicationName("AOD Stream Calendar");
        $client->setDeveloperKey(config('services.google.apiKey'));

        $service = new Google_Service_Calendar($client);

        $eventStream = $service->events
            ->listEvents(config('app.aod.stream_calendar'), [
                'timeMin' => now()->format(DateTimeInterface::RFC3339),
                'timeMax' => now()->addDays(7)->format(DateTimeInterface::RFC3339),
                'singleEvents' => true,
                'orderBy' => 'startTime'
            ]);

        $events = [];
        while (true) {
            /** @var Google_Service_Calendar_Event $event */
            foreach ($eventStream->getItems() as $event) {
                if ($event->summary || $event->description) {
                    $start = Carbon::parse($event->start->dateTime ?? $event->start->date);
                    $end = Carbon::parse($event->end->dateTime ?? $event->end->date);
                    $events[] = [
                        "event" => $event->summary ?? $event->description,
                        "time" => "{$start->format('M d @ h:i A')} - {$end->format('M d @ h:i A')}",
                        "timestamp-start" => $start->timestamp,
                        "timestamp-end" => $end->timestamp,
                    ];
                }
            }
            $pageToken = $eventStream->getNextPageToken();
            if ($pageToken) {
                $optParams = ['pageToken' => $pageToken];
                $events = $service->events->listEvents('primary', $optParams);
            } else {
                break;
            }
        }

        return response()->json($events);
    }
}
