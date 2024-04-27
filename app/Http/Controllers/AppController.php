<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class AppController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function changeLog()
    {
        return view('application.changelog');
    }

    /**
     * Show the application dashboard.
     *
     * @return Factory|View
     */
    public function index()
    {
        if (!$this->storageOwnershipValid()) {
            toastr()->error('Storage ownership issue. Tracker sync may not perform correctly.', 'Sync error');
        }

        $myDivision = \Auth::user()->member->division;

        $maxDays = config('app.aod.maximum_days_inactive');

        $myDivision->outstandingInactives = $myDivision->members()->whereDoesntHave('leave')
            ->where('last_ts_activity', '<', \Carbon\Carbon::now()->subDays($maxDays)->format('Y-m-d'))->count();

        $divisions = Division::active()->withoutFloaters()->withCount('members')
            ->orderBy('name')
            ->get()
            ->except($myDivision->id);

        return view('home.show', compact('divisions', 'myDivision'));
    }

    private function storageOwnershipValid()
    {
        $filePath = storage_path('database.sqlite');

        return file_exists($filePath) && app()->environment() === 'production'
            && posix_getpwuid(fileowner($filePath)) == 'nginx-data'
            && posix_getgrgid(filegroup($filePath)) == 'nginx';
    }
}
