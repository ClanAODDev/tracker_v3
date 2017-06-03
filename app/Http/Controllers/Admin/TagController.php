<?php

namespace App\Http\Controllers\Admin;

use App\Division;
use App\Http\Controllers\Controller;
use App\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Class TagController
 *
 * @package App\Http\Controllers\Admin
 */
class TagController extends Controller
{
    use AuthorizesRequests;

    /**
     * @param Request $request
     */
    public function update(Request $request)
    {
        $this->authorize('create', Division::class);

        $this->cleanDefaultTags();

        $this->createNewTags($request->default_tags);

        return redirect(route('admin') . '#tags');
    }

    /**
     * Erases existing default tags
     */
    private function cleanDefaultTags()
    {
        $tags = Tag::whereDefault(true)->get();
        $tags->each(function ($tag) {
            $tag->delete();
        });
    }

    /**
     * Synchronize new default tags
     *
     * @param $tags
     */
    private function createNewTags($tags)
    {
        collect(array_flatten($tags))->each(function ($tagName) {
            $tag = new Tag;
            $tag->name = $tagName;
            $tag->division_id = 0;
            $tag->default = true;
            $tag->save();
        });
    }
}
