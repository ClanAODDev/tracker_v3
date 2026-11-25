<?php

namespace App\Http\Controllers;

use App\Http\Requests\Training\CompleteTrainingRequest;
use App\Models\Member;
use App\Models\TrainingModule;
use App\Models\User;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class TrainingController extends Controller
{
    #[Authorize('train', User::class)]
    public function index(#[CurrentUser] User $user): Response
    {
        $member = $user->member;

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

    #[Authorize('train', User::class)]
    public function show(string $slug, Request $request, #[CurrentUser] User $user): Response
    {
        $module = TrainingModule::where('slug', $slug)
            ->where('is_active', true)
            ->with(['sections.checkpoints'])
            ->firstOrFail();

        abort_unless($module->isAccessibleBy($user->member), 403);

        $trainee = $request->filled('member')
            ? Member::where('guid', $request->string('member'))->first()
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
                'guid'     => $trainee->guid,
                'name'     => $trainee->name,
                'rankName' => $trainee->present()->rankName,
            ] : null,
        ]);
    }

    #[Authorize('train', User::class)]
    public function sgtTraining(Request $request, #[CurrentUser] User $user): Response
    {
        return $this->show('sgt', $request, $user);
    }

    public function update(CompleteTrainingRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        Member::where('guid', $request->validated('member'))->update([
            'last_trained_at'   => now(),
            'trainer_member_id' => $user->member_id,
        ]);

        $this->showSuccessToast('Training information successfully submitted!');

        return redirect('home');
    }
}
