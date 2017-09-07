<?php

namespace App\Http\Controllers\Slack;

use App\Http\Requests\Slack\ArchiveChannel;
use App\Http\Requests\Slack\CreateChannel;
use App\Http\Requests\Slack\UnarchiveChannel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use wrapi\slack\slack;

class SlackFilesController extends SlackController
{

    private $client;

    public function __construct(slack $client)
    {
        $this->client = $client;
        $this->middleware(['auth', 'admin']);
    }


    /**
     * List all slack files
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('manageSlack', auth()->user());

        $response = $this->client->files->list();

        $storage = array_sum(collect($response['files'])->map(function ($file) {
            return $file['size'];
        })->toArray());

        $percentUsage = number_format(($storage / 5368709120) * 100, 1);

        return view('slack.files', compact('storage', 'percentUsage'))->with(
            ['files' => $response['files']]
        );

    }

    /**
     * Mass murder of Slack files
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function purgeAll()
    {
        $response = $this->client->files->list();

        if ( ! count($response['files'])) {
            $this->showErrorToast('No files to delete!');

            return redirect()->route('slack.files');
        }

        foreach ($response['files'] as $file) {
            $this->client->files->delete(['file' => $file['id']]);
        }
        $this->showToast('Purged some files!');

        return redirect()->route('slack.files');
    }

    /**
     * Destroys a file from Slack
     *
     * @param $fileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($fileId)
    {
        $this->client->files->delete(['file' => $fileId]);
        $this->showToast('File has been deleted');

        return redirect()->route('slack.files');
    }
}
