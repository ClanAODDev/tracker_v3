<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use stdClass;
use Twig_Error;
use Twig_Error_Syntax;
use Twig_Sandbox_SecurityError;

/**
 * Class DivisionStructureController.
 */
class DivisionStructureController extends Controller
{
    use AuthorizesRequests;

    /**
     * DivisionStructureController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'activeDivision']);
    }

    /**
     * @return string
     *
     * @throws AuthorizationException
     */
    public function show(Division $division)
    {
        $this->authorize('viewDivisionStructure', auth()->user());

        try {
            $compiledData = $this->compileDivisionData($division);

            $templates = ['structure' => $division->structure];

            $env = new \Twig\Environment(new \Twig\Loader\ArrayLoader($templates), [
                'autoescape' => false,
            ]);

            $env->addFunction(new \Twig\TwigFunction('ordSuffix', fn ($value) => ordSuffix($value)));

            $env->addFunction(new \Twig\TwigFunction('replaceRegex', function ($str, $search, $replace = null) {
                // Are they using the standard Twig syntax?
                if (\is_array($search) && null === $replace) {
                    return strtr($str, $search);
                }
                // Is this a regular expression?
                if (preg_match('/^\/.+\/[a-zA-Z]*$/', $search)) {
                    return preg_replace($search, $replace, $str);
                }
                // Otherwise use str_replace
                return str_replace($search, $replace, $str);
            }));

            $data = $env->render('structure', ['division' => $compiledData]);
        } catch (Twig_Error $error) {
            $data = $this->handleTwigError($error);
        }

        return view('division.structure', compact('data', 'division'));
    }

    /**
     * @return Factory|View
     */
    public function modify(Division $division)
    {
        $this->authorize('editDivisionStructure', auth()->user());

        return view('division.structure-editor', compact('division'));
    }

    /**
     * @return Redirector|RedirectResponse
     */
    public function update(Request $request, Division $division)
    {
        $this->authorize('viewDivisionStructure', auth()->user());

        $division->structure = $request->structure;
        $division->save();

        $division->recordActivity('updated_structure');
        $this->showToast('Division structure was successfully updated!');

        return redirect(route('division.structure', $division->abbreviation));
    }

    /**
     * @return string
     */
    public function twigfiddleJson(Division $division)
    {
        return json_encode([
            'division' => [
                'name' => $division->name,
                'leaders' => $division->leaders,
                'generalSergeants' => $division->generalSergeants,
                'platoons' => $division->platoons()->with(
                    [
                        'squads.members.handles' => function ($query) use ($division) {
                            // filtering handles for just the relevant one
                            $query->where('id', $division->handle_id);
                        },
                    ],
                    'squads',
                    'squads.members',
                    'squads.members.rank'
                )->sortBy('order', 'asc')->get(),
            ],
        ]);
    }

    /**
     * @return stdClass
     */
    private function compileDivisionData(Division $division)
    {
        $data = new stdClass();
        $data->structure = $division->structure;
        $data->name = $division->name;
        $data->memberCount = $division->members->count();

        $data->leave = $this->getLeave($division);

        $data->locality = $this->getLocality($division);
        $data->generalSergeants = $division->generalSergeants()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'rank',
        ])->orderBy('rank_id', 'DESC')->orderBy('name', 'ASC')->get();

        $data->leaders = $division->leaders()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'rank',
        ])->get();

        $data->partTimeMembers = $division->partTimeMembers()->with([
            'handles' => $this->filterHandlesToPrimaryHandle($division),
            'rank',
        ])->get();

        $data->platoons = $division->platoons()->with([
            'squads.members.handles' => $this->filterHandlesToPrimaryHandle($division),
            'leader.handles' => $this->filterHandlesToPrimaryHandle($division),
            'squads.members.rank',
            'squads.leader.rank',
            'squads.leader.handles' => $this->filterHandlesToPrimaryHandle($division),
            'leader.rank',
        ])->get();

        /*
         * ensure squad leaders don't appear in squads.
         */
        $data->platoons = $this->filterSquadLeads($data);

        /*
         * have to do some funky things to get handles organized:
         * divisions only need the primary handle for a member.
         */
        $data->leaders = $data->leaders->each($this->getMemberHandle());
        $data->partTimeMembers = $data->partTimeMembers->each($this->getMemberHandle());
        $data->generalSergeants = $data->generalSergeants->each($this->getMemberHandle());

        // platoon->leader->handle
        $data->platoons = $data->platoons->each(function ($platoon) {
            if ($platoon->leader) {
                $platoon->leader->handle = $platoon->leader->handles->first();
            }
        });

        // squad->leader->handle
        // squad->member->handle
        $data->platoons = $data->platoons->each(function ($platoon) {
            $platoon->squads = $platoon->squads->each(function ($squad) {
                if ($squad->leader) {
                    $squad->leader->handle = $squad->leader->handles->first();
                }
                $squad->members = $squad->members->each($this->getMemberHandle());
            });
        });

        return $data;
    }

    /**
     * Filters leave, omitting unapproved leave.
     *
     * @param $division
     * @return mixed
     */
    private function getLeave($division)
    {
        $leave = $division->members()->whereHas('leave')
            ->with('leave', 'rank')->get();

        return $leave->filter(fn ($member) => $member->leave->approver);
    }

    private function getLocality(Division $division)
    {
        return [
            'squad' => $division->locality('squad'),
            'platoon' => $division->locality('platoon'),
            'squad_leader' => $division->locality('squad leader'),
            'platoon_leader' => $division->locality('platoon leader'),
        ];
    }

    /**
     * Eager loading filter.
     *
     * @param $division
     * @return Closure
     */
    private function filterHandlesToPrimaryHandle($division)
    {
        return function ($query) use ($division) {
            $query->where('id', $division->handle_id);
        };
    }

    /**
     * @param $data
     * @return mixed
     */
    private function filterSquadLeads($data)
    {
        return $data->platoons->each(function ($platoon) {
            $platoon->squads = $platoon->squads->each(function ($squad) {
                $squad->members = $squad->members->filter(fn ($member) => $member->clan_id !== $squad->leader_id);
            });
        });
    }

    /**
     * @return Closure
     */
    private function getMemberHandle()
    {
        return function ($member) {
            $member->handle = $member->handles->first();
        };
    }

    /**
     * @param $error
     * @return string
     */
    private function handleTwigError($error)
    {
        if ($error instanceof Twig_Error_Syntax) {
            return $error->getMessage();
        }

        if ($error instanceof Twig_Sandbox_SecurityError) {
            return 'You attempted to use an unauthorized tag, filter, or method';
        }

        return $error->getMessage();
    }
}
