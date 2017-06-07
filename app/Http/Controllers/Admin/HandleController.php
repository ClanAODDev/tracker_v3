<?php

namespace App\Http\Controllers\Admin;

use App\Handle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class HandleController
 *
 * @package App\Http\Controllers\Admin
 */
class HandleController extends Controller
{
    /**
     * @param Handle $handle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Handle $handle)
    {
        return view('admin.modify-handle', compact('handle'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.create-handle');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        // @TODO: form validation, form request object

        Handle::create($request->all());

        $this->showToast('Handle created!');

        return redirect(route('admin') . '#handles');
    }

    /**
     * @param Handle $handle
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Handle $handle, Request $request)
    {
        // @TODO: form validation, form request object

        $handle->update($request->all());

        return redirect(route('admin') . '#handles');
    }

    /**
     * @param Handle $handle
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Handle $handle)
    {
        if ($handle->divisions->count()) {
            $this->showErrorToast('Handle still used by divisions');

            return redirect(route('admin') . '#handles');
        }

        $handle->delete();

        $this->showToast('Handle deleted');

        return redirect(route('admin') . '#handles');
    }
}
