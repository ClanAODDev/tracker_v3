<?php

namespace App\Http\Controllers\Slack;

use App\Http\Controllers\Controller;
use App\SlackSlashCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SlackCommandController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if (!$this->isValid($request)) {
            return response()->json([
                'text' => 'Unrecognized command. Sorry!',
            ]);
        }

        $command = Arr::first(
            explode(':', $request->text)
        );

        $response = SlackSlashCommand::handle($command, $request->all());

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isValid(Request $request)
    {
        if (Arr::has($request->all(), 'text')) {
            return true;
        }
    }
}
