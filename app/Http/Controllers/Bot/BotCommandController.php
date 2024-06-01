<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Models\BotSlashCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotCommandController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(Request $request, $command)
    {
        $response = BotSlashCommand::handle($command, $request);

        return response()->json($response);
    }
}
