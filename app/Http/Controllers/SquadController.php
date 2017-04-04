<?php

namespace App\Http\Controllers;

use Toastr;
use App\Squad;
use App\Member;
use App\Platoon;
use App\Division;

use App\Http\Requests\CreateSquadForm;
use App\Http\Requests\DeleteSquadForm;
use App\Http\Requests\UpdateSquadForm;

class SquadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function index(Division $division, Platoon $platoon)
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\Response
     */
    public function create(Division $division, Platoon $platoon)
    {
        $this->authorize('create', [Squad::class, $division]);

        return view('squad.create', compact('division', 'platoon'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateSquadForm $form
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateSquadForm $form, Division $division, Platoon $platoon)
    {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['leader_id' => "Member {$form->leader_id} not assigned to this division!"])
                ->withInput();
        }

        $form->persist();

        Toastr::success(
            ucwords($division->locality('squad')) . " has been created!",
            "Success",
            ['positionClass' => 'toast-top-right', 'progressBar' => true]
        );

        return redirect()->route('platoonSquads', [$division->abbreviation, $platoon]);
    }

    /**
     * @param $request
     * @param Division $division
     * @return bool
     */
    public function isMemberOfDivision(Division $division, $request)
    {
        $member = Member::whereClanId($request->leader_id)->first();

        return $member->primaryDivision instanceof Division &&
            $member->primaryDivision->id === $division->id;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Division $division
     * @param Platoon $platoon
     * @param Squad $squad
     * @return \Illuminate\Http\Response
     */
    public function edit(Division $division, Platoon $platoon, Squad $squad)
    {
        $this->authorize('update', $squad);

        return view('squad.edit', compact('squad', 'platoon', 'division'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSquadForm $form
     * @param Division $division
     * @param Platoon $platoon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(
        UpdateSquadForm $form,
        Division $division,
        Platoon $platoon,
        Squad $squad
    ) {
        if ($form->leader_id && ! $this->isMemberOfDivision($division, $form)) {
            return redirect()->back()
                ->withErrors(['leader_id' => "Member {$form->leader_id} not assigned to this division!"])
                ->withInput();
        }

        $form->persist();

        Toastr::success(
            ucwords($squad->name) . " has been updated!",
            "Success",
            ['positionClass' => 'toast-top-right', 'progressBar' => true]
        );

        return redirect()->route('platoonSquads', [$division->abbreviation, $platoon]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteSquadForm $form
     * @param Division $division
     * @param Platoon $platoon
     * @param Squad $squad
     * @return \Illuminate\Http\Response
     */
    public function destroy(
        DeleteSquadForm $form,
        Division $division,
        Platoon $platoon,
        Squad $squad
    ) {
        $form->persist();

        Toastr::success(
            ucwords($squad->name) . " has been deleted!",
            "Success",
            ['positionClass' => 'toast-top-right', 'progressBar' => true]
        );

        return redirect()->route('platoonSquads', [$division->abbreviation, $platoon]);
    }
}
