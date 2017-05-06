<?php

namespace App\Http\Controllers\Admin;

use App\Division;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDivisionForm;
use App\Http\Requests\CreateDivision;

class DivisionController extends Controller
{
    public function edit(Division $division)
    {
        $this->authorize('show', $division);

        return view('admin.modify-division', compact('division'));
    }

    public function store(CreateDivision $form)
    {
        $form->persist();

        $this->showToast("New division has been created!");

        return redirect(route('admin') . '#divisions');
    }

    public function destroy(Division $division)
    {
        $this->authorize('delete', $division);

        $division->delete();

        $this->showToast("Division has been destroyed");

        return redirect(route('admin') . '#divisions');
    }

    public function create()
    {
        $this->authorize('create', Division::class);

        return view('admin.create-division');

    }

    /**
     * @param UpdateDivisionForm $form
     * @param Division $division
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateDivisionForm $form, Division $division)
    {
        $form->persist();

        $this->showToast("Division was updated!");

        return redirect(route('admin') . '#divisions');
    }
}
