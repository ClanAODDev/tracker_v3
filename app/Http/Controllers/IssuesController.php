<?php

namespace App\Http\Controllers;

use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Http\Request;

class IssuesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $issues = GitHub::issues()->all('flashadvocate', 'tracker_v3', ['labels' => 'bug']);

        return view('issues.index', compact('issues'));

    }
}
