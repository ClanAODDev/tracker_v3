<?php

namespace App\Http\Controllers;

use App\User;
use Github\Exception\RuntimeException;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class IssuesController
 *
 * @package App\Http\Controllers
 */
class IssuesController extends Controller
{

    use AuthorizesRequests;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|string
     */
    public function index()
    {
        $this->authorize('manage-issues', User::class);

        // , ['labels' => 'bug']
        $issues = GitHub::issues()->all('flashadvocate', 'tracker_v3');

        return view('issues.index', compact('issues'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(
        Request $request
    ) {
        $this->authorize('manage-issues', User::class);

        try {
            GitHub::issues()->create('flashadvocate', 'tracker_v3', [
                'title' => $request->title,
                'body' => $request->body . "\r\n\r\nReported by: " . auth()->user()->name,
                'labels' => [$request->labels]
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
