<?php

namespace App\Http\Controllers;

class TrainingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('train', auth()->user());

        return view('training.index');
    }

    public function sgtTraining()
    {
        $this->authorize('train', auth()->user());

        return view('training.sgt-training');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(\Illuminate\Http\Request $request)
    {
        $request->validate(['clan_id' => 'exists:members,clan_id'], [
            'clan_id.exists' => 'That member id appears to be invalid',
        ]);

        \App\Models\Member::whereClanId($request->clan_id)->update([
            'last_trained_at' => now(),
            'last_trained_by' => auth()->user()->member->clan_id,
        ]);

        $this->showToast('Training information successfully submitted!');

        return redirect('home');
    }
}
