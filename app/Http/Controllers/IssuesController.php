<?php

namespace App\Http\Controllers;

use Github\Exception\RuntimeException;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function create(Request $request)
    {
        try {
            GitHub::issues()->create('flashadvocate', 'tracker_v3', [
                'title' => $request->title,
                'body' => $request->body . "\r\n\r\nReported by: " . auth()->user()->name,
                'labels' => ['bug']
            ]);
        } catch (RuntimeException $exception) {

            $this->showErrorToast('Something went wrong...');

            Log::error("Github issue report error - " . $exception->getMessage());

            return redirect()->back();
        }

        $this->showToast('Your issue was reported successfully');

        return redirect()->back();
    }
}
