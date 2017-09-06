<?php

namespace App\Http\Controllers\Admin;

use App\Division;
use App\Handle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDivisionForm;
use App\Http\Requests\CreateDivision;

class DivisionController extends Controller
{
    public function edit(Division $division)
    {
        $this->authorize('show', $division);

        $handles = Handle::all()->pluck('label', 'id');

        return view('admin.modify-division', compact('division', 'handles'));
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

        $handles = Handle::all()->pluck('label', 'id');

        return view('admin.create-division', compact('handles'));
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
        $division->recordActivity('updated_admin_settings');

        return redirect(route('admin') . '#divisions');
    }
}
