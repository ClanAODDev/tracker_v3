<?php

namespace App\Http\Controllers;

use App\Http\Requests\Training\CompleteTrainingRequest;
use App\Models\Member;
use App\Models\TrainingModule;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class TrainingController extends Controller
{
    public function index(): Response
    {
        $this->authorize('train', auth()->user());

        $member = auth()->user()->member;

        $modules = TrainingModule::active()->ordered()->withCount('sections')->get()
            ->filter(fn ($module) => $module->isAccessibleBy($member))
            ->values()
            ->map(fn (TrainingModule $module) => [
                'slug'          => $module->slug,
                'name'          => $module->name,
                'description'   => $module->description,
                'sectionsCount' => $module->sections_count,
            ]);

        return Inertia::render('training/index', ['modules' => $modules]);
    }

    public function show(string $slug, Request $request): Response
    {
        $this->authorize('train', auth()->user());

        $module = TrainingModule::where('slug', $slug)
            ->where('is_active', true)
            ->with(['sections.checkpoints'])
            ->firstOrFail();

        abort_unless($module->isAccessibleBy(auth()->user()->member), 403);

        $trainee = $request->filled('clan_id')
            ? Member::where('clan_id', $request->clan_id)->first()
            : null;

        return Inertia::render('training/module', [
            'module' => [
                'slug'               => $module->slug,
                'name'               => $module->name,
                'checkpointLabel'    => $module->checkpoint_label,
                'showCompletionForm' => (bool) $module->show_completion_form,
                'sections'           => $module->sections->map(fn ($section) => [
                    'title'       => $section->title,
                    'content'     => (string) Markdown::convertToHtml($section->content ?? ''),
                    'checkpoints' => $section->checkpoints->map(fn ($checkpoint) => [
                        'label'       => $checkpoint->label,
                        'description' => $checkpoint->description
                            ? (string) Markdown::convertToHtml($checkpoint->description)
                            : null,
                    ])->values(),
                ])->values(),
            ],
            'trainee' => $trainee ? [
                'clanId'   => $trainee->clan_id,
                'name'     => $trainee->name,
                'rankName' => $trainee->present()->rankName,
            ] : null,
        ]);
    }

    public function sgtTraining(Request $request): Response
    {
        return $this->show('sgt', $request);
    }

    public function update(CompleteTrainingRequest $request): RedirectResponse
    {
        Member::whereClanId($request->clan_id)->update([
            'last_trained_at' => now(),
            'last_trained_by' => auth()->user()->member->clan_id,
        ]);

        $this->showSuccessToast('Training information successfully submitted!');

        return redirect('home');
    }
}
