<?php

namespace App\Http\Controllers\Slack;

use App\Http\Controllers\Controller;
use App\SlackSlashCommand;
use Illuminate\Http\Request;

class SlackController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (! $this->isValid($request)) {
            return response()->json([
                'text' => 'Unrecognized command. Sorry!',
            ]);
        }

        $command = array_first(
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
        if (array_has($request->all(), 'text')) {
            return true;
        }
    }
}
