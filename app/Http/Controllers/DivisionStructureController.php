<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Models\Division;
use Closure;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use stdClass;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

class DivisionStructureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Division $division): View
    {
        $this->authorize('viewDivisionStructure', auth()->user());

        $data = null;

        try {
            $data = $this->renderTemplate($division, $division->structure ?? '');
        } catch (Exception $error) {
            $this->showErrorToast($error instanceof SyntaxError
                ? $error->getMessage()
                : 'An error occurred rendering the template');
        }

        $stats = (object) [
            'members' => $division->members()->count(),
            'platoons' => $division->platoons()->count(),
            'squads' => $division->squads()->count(),
            'leaders' => $division->leaders()->count(),
        ];

        $lastUpdated = $division->activity()
            ->where('name', 'updated_structure')
            ->latest()
            ->first();

        return view('division.structure', compact('data', 'division', 'stats', 'lastUpdated'));
    }

    public function modify(Division $division): View
    {
        $this->authorize('editDivisionStructure', auth()->user());

        return view('division.structure-editor', compact('division'));
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $this->authorize('viewDivisionStructure', auth()->user());

        $division->structure = $request->structure;
        $division->save();

        $division->recordActivity(ActivityType::UPDATED_STRUCTURE);
        $this->showSuccessToast('Division structure was successfully updated!');

        return redirect(route('division.edit-structure', $division->slug));
    }

    public function preview(Request $request, Division $division)
    {
        $this->authorize('editDivisionStructure', auth()->user());

        try {
            $output = $this->renderTemplate($division, $request->input('template') ?? '');

            return response()->json([
                'success' => true,
                'output' => $output,
                'characters' => strlen($output),
            ]);
        } catch (Exception $error) {
            return response()->json([
                'success' => false,
                'error' => $error->getMessage(),
            ]);
        }
    }

    private function renderTemplate(Division $division, string $template): string
    {
        $compiledData = $this->compileDivisionData($division);
        $env = $this->createTwigEnvironment($template);
        $output = $env->render('structure', ['division' => $compiledData]);

        return $this->normalizeOutput($output);
    }

    private function createTwigEnvironment(string $template): Environment
    {
        $env = new Environment(new ArrayLoader(['structure' => $template]), [
            'autoescape' => false,
        ]);

        $env->addFunction(new TwigFunction('ordSuffix', fn ($value) => ordSuffix($value)));

        $env->addFunction(new TwigFunction('replaceRegex', function ($str, $search, $replace = null) {
            if (\is_array($search) && $replace === null) {
                return strtr($str, $search);
            }

            if (preg_match('/^\/.+\/[a-zA-Z]*$/', $search)) {
                return preg_replace($search, $replace, $str);
            }

            return str_replace($search, $replace, $str);
        }));

        return $env;
    }

    private function compileDivisionData(Division $division): stdClass
    {
        $data = new stdClass;
        $data->structure = $division->structure;
        $data->name = $division->name;
        $data->memberCount = $division->members->count();
        $data->leave = $this->getLeave($division);
        $data->locality = $this->getLocality($division);

        $handleFilter = $this->filterHandlesToPrimaryHandle($division);

        $data->generalSergeants = $division->generalSergeants()
            ->with(['handles' => $handleFilter])
            ->orderBy('rank', 'DESC')
            ->orderBy('name', 'ASC')
            ->get();

        $data->leaders = $division->leaders()
            ->with(['handles' => $handleFilter])
            ->get();

        $data->partTimeMembers = $division->partTimeMembers()
            ->with(['handles' => $handleFilter])
            ->get();

        $data->platoons = $division->platoons()->with([
            'squads.members.handles' => $handleFilter,
            'leader.handles' => $handleFilter,
            'squads.leader.handles' => $handleFilter,
        ])->get();

        $data->platoons = $this->filterSquadLeads($data);

        $data->leaders = $data->leaders->each($this->getMemberHandle());
        $data->partTimeMembers = $data->partTimeMembers->each($this->getMemberHandle());
        $data->generalSergeants = $data->generalSergeants->each($this->getMemberHandle());

        $data->platoons = $data->platoons->each(function ($platoon) {
            if ($platoon->leader) {
                $platoon->leader->handle = $platoon->leader->handles->first();
            }

            $platoon->squads = $platoon->squads->each(function ($squad) {
                if ($squad->leader) {
                    $squad->leader->handle = $squad->leader->handles->first();
                }
                $squad->members = $squad->members->each($this->getMemberHandle());
            });
        });

        return $data;
    }

    private function getLeave(Division $division)
    {
        return $division->members()
            ->whereHas('leave')
            ->with('leave')
            ->get()
            ->filter(fn ($member) => $member->leave->approver);
    }

    private function getLocality(Division $division): array
    {
        return [
            'squad' => $division->locality('squad'),
            'platoon' => $division->locality('platoon'),
            'squad_leader' => $division->locality('squad leader'),
            'platoon_leader' => $division->locality('platoon leader'),
        ];
    }

    private function filterHandlesToPrimaryHandle(Division $division): Closure
    {
        return fn ($query) => $query
            ->where('handles.id', $division->handle_id)
            ->wherePivot('primary', true);
    }

    private function filterSquadLeads(stdClass $data)
    {
        return $data->platoons->each(function ($platoon) {
            $platoon->squads = $platoon->squads->each(function ($squad) {
                $squad->members = $squad->members->filter(
                    fn ($member) => $member->clan_id !== $squad->leader_id
                );
            });
        });
    }

    private function getMemberHandle(): Closure
    {
        return fn ($member) => $member->handle = $member->handles->first();
    }

    private function normalizeOutput(string $output): string
    {
        $lines = explode("\n", $output);
        $normalized = [];

        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            $leadingSpaces = strlen($line) - strlen($trimmed);
            $reducedIndent = (int) floor($leadingSpaces / 2);
            $normalized[] = str_repeat(' ', $reducedIndent) . $trimmed;
        }

        $output = implode("\n", $normalized);
        $output = preg_replace("/\n{3,}/", "\n\n", $output);

        return trim($output);
    }
}
