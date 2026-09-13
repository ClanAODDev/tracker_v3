# Contributing Docs

Documentation pages are Markdown files rendered into the Tracker's React shell. Adding or editing one takes a few steps.

### Editing an existing page

Find its Markdown source under `resources/views/help/` — for example this page lives at
`resources/views/help/admin/partials/contributing.md`. Edit the Markdown and the change is live on the next request; no build step is required for content.

Follow the [kitchen sink](/help/docs/admin/sink) for supported syntax.

### Adding a new page

**1. Write the content.** Create a Markdown file under `resources/views/help/` (use `md-partials/` for member-facing docs, `admin/partials/` for admin-only docs).

**2. Add a controller method.** In `app/Http/Controllers/HelpController.php`, add a method that renders it:

```php
public function myNewDoc(): Response
{
    return $this->doc('md-partials/my-new-doc.md', 'My New Doc');
}
```

Admin-only pages pass `'Admin documentation'` as the third `doc()` argument for the eyebrow label.

**3. Register the route.** In `routes/partials/documentation.php`, add an entry inside the `HelpController` group (put admin pages inside the `admin` middleware group):

```php
Route::get('my-new-doc', 'myNewDoc')->name('help.my-new-doc');
```

**4. Link it.** Add an entry to the `Documentation` section of `app/Support/Navigation.php`, and — for member-facing docs — a card in `resources/js/pages/help/index.tsx`.
