<?php

namespace App\Http\Controllers\Admin;

use App\Handle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HandleController extends Controller
{
    public function edit(Handle $handle)
    {
        dd($handle);
    }
}
