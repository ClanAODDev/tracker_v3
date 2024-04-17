# Contributing Docs

Documentation is contributed with just a few steps, provided below. Markdown formatting is supported and preferable
for ease of consumption and maintenance.

### For new pages
#### View
Create a view in the `resources/views/help/admin` directory using the `<name.blade.php>` convention. You can copy the
   index file for convenience, and update the path if you want to write strictly markdown

Follow the examples in the [kitchen sink](/help/docs/admin/sink) for basic syntax.

#### Route
Add a route to your page in `routes/partials/documentation.php`. Use the existing entries as a guide. 

```javascript
// in order - the route, the dot-notation path to the view, and a route name (optional)
Route::view('sink', 'help.admin.sink')->name('help.admin.sink');
```  
  
#### Navigation link
Next, add an entry in the navigation blade partial at `/routes/views/application/partials/navigation.blade.php`. 
  Find the section marked as admin documentation. An example is provided below:

```html
<li class="{{ set_active(['help/docs/admin/sink']) }}">
    <a href="{{ route('help.admin.sink') }}">Kitchen Sink</a>
</li>
```

### For existing pages
If you just want to create a new section for an existing page, consider building a partial so the content can be 
organized according to topic. As an example, this page is included in the `resources/views/help/admin/index.blade.php` 
view 
using the 
`@include` blade directive. This way, we can write strictly as a `.md` file so IDEs can color-code, format, etc.

Just include the path to the partial wherever you want the content to be inserted.

```javascript
@include('help.admin.partials.contributing')
```

Alternatively, you can use the `@markdown` and `@endmarkdown` blade directives to directly add Markdown-formatted
content to a blade template.

```markdown
@markdown
    # My header
    - some
    - **basic**
    - __content__
@endmarkdown
```