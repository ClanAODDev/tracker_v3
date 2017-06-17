<link rel="stylesheet" type="text/css"
      href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.26.0/codemirror.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.26.0/theme/dracula.min.css">

@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <p>Enter your twig template in the text area below. </p>
                <form action="{{ route('division.update-structure', $division->abbreviation) }}" method="post">
                <textarea name="structure" id="code" name="code" class="form-control" rows="10"
                          style="font-family: Menlo, Monaco, Consolas, monospace; resize: vertical;"
                >{{ $division->structure }}</textarea>
                </form>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <pre><code name="generated-structure">{# Generated #}</code></pre>
            </div>
        </div>
    </div>

    <script>

      CodeMirror.fromTextArea(document.getElementById('code'), {
        mode: {name: 'twig', htmlMode: true},
        lineNumbers: true,
        theme: 'dracula'
      })

      $('name[generate-code]').click(function (e) {
        e.preventDefault()

      });
    </script>

@stop