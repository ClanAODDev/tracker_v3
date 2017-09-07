<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Slack\SlackChannelController;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Toastr;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param $toastMessage
     */
    protected function showToast($toastMessage)
    {
        Toastr::success(
            $toastMessage,
            "Success"
        );
    }

    protected function showErrorToast($toastMessage)
    {
        Toastr::error(
            $toastMessage,
            "Uh oh...",
            [
                'timeOut' => 10000
            ]
        );
    }
}
