<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;
use Twig_Error;
use Twig_Error_Syntax;
use Twig_Loader_String;
use Twig_Sandbox_SecurityError;
use TwigBridge\Facade\Twig;

/**
 * Class DivisionStructureController
 *
 * @package App\Http\Controllers
 */
class DivisionStructureController extends Controller
{
    /**
     * @param Division $division
     * @return string
     */
    public function show(Division $division)
    {

        Twig::setLoader(new Twig_Loader_String());

        try {
            $data = new \stdClass();
            $data->structure = $division->structure;
            $data->name = $division->name;
            $data->leaders = $division->leaders();
            $data->generalSergeants = $division->generalSergeants();
            $data->platoons = $division->platoons()->with(
                [
                    'squads.members.handles' => function ($query) use ($division) {
                        // filtering handles for just the relevant one
                        $query->where('id', $division->handle_id);
                    }
                ],
                'squads', 'squads.members', 'squads.members.rank'
            )->get();

            $division = $data;

            return Twig::render($data->structure, compact('division'));
        } catch (Twig_Error $error) {
            return $this->handleTwigError($error);
        }
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
            return "You attempted to use an unauthorized tag, filter, or method";
        }

        return $error->getMessage();
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function modify(Division $division)
    {
        if ( ! auth()->user()->isRole(['sr_ldr', 'admin'])) {
            abort(403);
        }

        return view('division.structure-editor', compact('division'));
    }

    /**
     * @param Request $request
     * @param Division $division
     */
    public function update(Request $request, Division $division)
    {
        $division->structure = $request->structure;
        $division->save();

        //return view('')
    }

    public function twigfiddleJson(Division $division)
    {
        return json_encode([
            "division" => [
                "name" => $division->name,
                "leaders" => $division->leaders(),
                "generalSergeants" => $division->generalSergeants(),
                "platoons" => $division->platoons()->with(
                    [
                        'squads.members.handles' => function ($query) use ($division) {
                            // filtering handles for just the relevant one
                            $query->where('id', $division->handle_id);
                        }
                    ],
                    'squads', 'squads.members', 'squads.members.rank'
                )->get()
            ]
        ]);
    }
}
