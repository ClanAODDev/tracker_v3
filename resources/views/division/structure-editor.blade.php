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

        <button type="button" name="generate-code" class="btn btn-success">Save</button>
        <a href="{{ route('division.structure', $division->abbreviation) }}" type="button"
           class="btn btn-default">View Generated Code</a>
    </div>

    <script>

      $(function () {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        })

        var cm = CodeMirror.fromTextArea(document.getElementById('code'), {
          mode: {name: 'twig', htmlMode: true},
          lineNumbers: true,
          theme: 'dracula'
        })

        cm.on('change', handleSave)

        function handleSave () {
          cm.save()
        }

        $('[name=generate-code]').click(function (e) {

          $.post('{{ route('division.update-structure', $division->abbreviation) }}',
            {structure: $('#code').val()}
          ).done(function () {
            toastr.success('Structure template has been saved!')
              {{--window.location.href = "{{ route('division.structure', $division->abbreviation) }}"--}}
          }).fail(function (error) {
            toastr.error(error.message)
          })
        })
      })
    </script>

@stop