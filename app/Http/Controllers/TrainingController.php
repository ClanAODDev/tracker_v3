<?php

namespace App\Http\Controllers;

class TrainingController extends Controller
{
    public function index()
    {
        $this->authorize('train', auth()->user());

        return view('training.index');
    }
}
