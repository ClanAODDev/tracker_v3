<?php

namespace App\Http\Controllers;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class HelpController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('help/index');
    }

    public function memberAwards(): Response
    {
        return $this->doc('md-partials/member-awards.md', 'Awards Images');
    }

    public function managingRank(): Response
    {
        return $this->doc('md-partials/managing-rank.md', 'Managing Rank');
    }

    public function recruiting(): Response
    {
        return $this->doc('md-partials/recruiting.md', 'Recruiting New Members');
    }

    public function adminContributing(): Response
    {
        return $this->doc('admin/partials/contributing.md', 'Contributing Documentation', 'Admin documentation');
    }

    public function adminChecklist(): Response
    {
        return $this->doc('admin/partials/division-checklist.md', 'New Division Checklist', 'Admin documentation');
    }

    public function adminSink(): Response
    {
        return $this->doc('admin/partials/sink.md', 'Kitchen Sink', 'Admin documentation');
    }

    private function doc(string $relPath, string $title, string $eyebrow = 'Documentation'): Response
    {
        $markdown = (string) file_get_contents(resource_path("views/help/{$relPath}"));

        return Inertia::render('help/doc', [
            'title'   => $title,
            'eyebrow' => $eyebrow,
            'body'    => (string) Markdown::convertToHtml($markdown),
        ]);
    }
}
