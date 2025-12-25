@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Structure Editor
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('division-structure', $division) !!}

        <div class="structure-editor-container">
            <div class="structure-editor-pane">
                <div class="structure-pane-header">
                    <h5><i class="fa fa-code"></i> Template</h5>
                    <div class="structure-pane-actions">
                        <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#reference-modal">
                            <i class="fa fa-book"></i> Reference
                        </button>
                    </div>
                </div>
                <form action="{{ route('division.update-structure', $division->slug) }}" method="post" id="structure-form">
                    @csrf
                    <textarea name="structure" id="code" class="form-control">{{ $division->structure }}</textarea>
                    <div class="structure-editor-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save Template
                        </button>
                        <a href="{{ route('division.structure', $division->slug) }}" class="btn btn-default">
                            <i class="fa fa-eye"></i> View Output
                        </a>
                    </div>
                </form>
            </div>

            <div class="structure-preview-pane">
                <div class="structure-pane-header">
                    <h5><i class="fa fa-desktop"></i> Preview</h5>
                    <div class="structure-pane-status">
                        <span id="preview-status" class="text-muted"></span>
                        <span id="char-count" class="text-muted"></span>
                    </div>
                </div>
                <div class="structure-preview-content">
                    <textarea id="preview-output" class="form-control" readonly></textarea>
                </div>
                <div class="structure-preview-footer">
                    <button type="button" class="btn btn-success copy-to-clipboard" data-clipboard-target="#preview-output">
                        <i class="fa fa-clone"></i> Copy Output
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="reference-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-book"></i> Template Reference</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Division Variables</h6>
                            <table class="table table-condensed">
                                <tr><td><code>division.name</code></td><td>Division name</td></tr>
                                <tr><td><code>division.memberCount</code></td><td>Total member count</td></tr>
                                <tr><td><code>division.leaders</code></td><td>Division leaders (CO/XO)</td></tr>
                                <tr><td><code>division.generalSergeants</code></td><td>General sergeants</td></tr>
                                <tr><td><code>division.partTimeMembers</code></td><td>Part-time members</td></tr>
                                <tr><td><code>division.platoons</code></td><td>All platoons</td></tr>
                                <tr><td><code>division.leave</code></td><td>Members on leave</td></tr>
                                <tr><td><code>division.locality</code></td><td>Custom labels</td></tr>
                            </table>

                            <h6>Platoon Properties</h6>
                            <table class="table table-condensed">
                                <tr><td><code>platoon.name</code></td><td>Platoon name</td></tr>
                                <tr><td><code>platoon.leader</code></td><td>Platoon leader</td></tr>
                                <tr><td><code>platoon.squads</code></td><td>Squads in platoon</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Squad Properties</h6>
                            <table class="table table-condensed">
                                <tr><td><code>squad.name</code></td><td>Squad name</td></tr>
                                <tr><td><code>squad.leader</code></td><td>Squad leader</td></tr>
                                <tr><td><code>squad.members</code></td><td>Squad members</td></tr>
                            </table>

                            <h6>Member Properties</h6>
                            <table class="table table-condensed">
                                <tr><td><code>member.name</code></td><td>Forum name</td></tr>
                                <tr><td><code>member.handle</code></td><td>Game handle</td></tr>
                                <tr><td><code>member.rank</code></td><td>Rank object</td></tr>
                            </table>

                            <h6>Helper Functions</h6>
                            <table class="table table-condensed">
                                <tr><td><code>ordSuffix(n)</code></td><td>Adds ordinal suffix (1st, 2nd, 3rd)</td></tr>
                                <tr><td><code>replaceRegex(str, pattern, replace)</code></td><td>Regex replacement</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/twig/twig.min.js"></script>

<script>
$(function () {
    var previewUrl = "{{ route('division.preview-structure', $division->slug) }}";
    var debounceTimer = null;
    var $status = $('#preview-status');
    var $charCount = $('#char-count');
    var $preview = $('#preview-output');

    var cm = CodeMirror.fromTextArea(document.getElementById('code'), {
        mode: {name: 'twig', htmlMode: true},
        lineNumbers: true,
        theme: 'dracula',
        lineWrapping: true
    });

    function updatePreview() {
        $status.html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: previewUrl,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                template: cm.getValue()
            },
            success: function(response) {
                if (response.success) {
                    $preview.val(response.output);
                    $charCount.text(response.characters + ' characters');
                    $status.html('<i class="fa fa-check text-success"></i>');
                } else {
                    $preview.val('Error: ' + response.error);
                    $status.html('<i class="fa fa-exclamation-triangle text-warning"></i> Syntax error');
                    $charCount.text('');
                }
            },
            error: function() {
                $status.html('<i class="fa fa-times text-danger"></i> Request failed');
            }
        });
    }

    cm.on('change', function() {
        if (debounceTimer) clearTimeout(debounceTimer);
        $status.html('<i class="fa fa-circle text-muted"></i> Typing...');
        debounceTimer = setTimeout(updatePreview, 500);
    });

    updatePreview();
});
</script>
@endsection
