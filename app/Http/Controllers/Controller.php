<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Toastr;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function showSuccessToast($message, $title = 'Success')
    {
        Toastr::success($message, $title);
    }

    protected function showInfoToast($message, $title = 'Info')
    {
        Toastr::info($message, $title);
    }

    protected function showErrorToast($message, $title = 'Uh oh...')
    {
        Toastr::error($message, $title, [
            'timeOut' => 10000,
        ]);
    }

    protected function showImportantToast($message, $title)
    {
        Toastr::info($message, $title, [
            'timeOut' => 0,
            'extendedTimeOut' => 0,
        ]);
    }
}
