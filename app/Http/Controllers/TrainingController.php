<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TrainingModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(string $slug, Request $request)
    {
        $this->authorize('train', auth()->user());

        $module = TrainingModule::where('slug', $slug)
            ->where('is_active', true)
            ->with(['sections.checkpoints'])
            ->firstOrFail();

        $trainee = null;
        if ($request->has('clan_id')) {
            $trainee = Member::where('clan_id', $request->clan_id)->first();
        }

        return view('training.module', compact('module', 'trainee'));
    }

    public function sgtTraining(Request $request)
    {
        return $this->show('sgt', $request);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['clan_id' => 'required|exists:members,clan_id'], [
            'clan_id.required' => 'Please select a member',
            'clan_id.exists' => 'That member appears to be invalid',
        ]);

        Member::whereClanId($request->clan_id)->update([
            'last_trained_at' => now(),
            'last_trained_by' => auth()->user()->member->clan_id,
        ]);

        $this->showSuccessToast('Training information successfully submitted!');

        return redirect('home');
    }
}
