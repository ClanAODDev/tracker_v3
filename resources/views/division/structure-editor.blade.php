<link rel="stylesheet" type="text/css"
      href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.26.0/codemirror.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.26.0/theme/dracula.min.css">

@extends('application.base-tracker')

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

    <form action="{{ route('division.update-structure', $division->slug) }}" method="post">
        {{ csrf_field() }}
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <p>Enter your twig template in the text area below. </p>
                    <textarea name="structure" id="code" name="code" class="form-control" rows="10"
                              style="font-family: Menlo, Monaco, Consolas, monospace; resize: vertical;"
                    >{{ $division->structure }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 m-t-md">
                    <button type="submit" name="generate-code" class="btn btn-success"><i
                                class="fa fa-save text-accent"></i> Save
                    </button>
                </div>
            </div>

        </div>
    </form>

    <script>

      $(function () {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        var cm = CodeMirror.fromTextArea(document.getElementById('code'), {
          mode: {name: 'twig', htmlMode: true},
          lineNumbers: true,
          theme: 'dracula'
        });

        cm.on('change', handleSave);

        function handleSave () {
          cm.save();
        }
      });
    </script>

@endsection