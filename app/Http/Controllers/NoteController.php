<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNote;

class NoteController extends Controller
{
    /**
     * @param CreateNote $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateNote $form)
    {
        $form->persist();

        return redirect()->back();

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}
