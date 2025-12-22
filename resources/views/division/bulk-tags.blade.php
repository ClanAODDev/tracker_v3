@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $division->name }}
        @endslot
        @slot ('subheading')
            {{ $members->count() === 1 ? 'Manage Tags' : 'Bulk Tag Assignment' }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-8">

                @php
                    $isSeniorLeader = auth()->user()->isRole(['admin', 'sr_ldr']);
                @endphp
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        Select Tags
                    </div>
                    <div class="panel-body" id="tags-list">
                        @forelse ($tags as $tag)
                            <label class="tag-checkbox">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" form="bulk-tags-form">
                                <span class="badge tag-visibility-{{ $tag->visibility->value }}">{{ $tag->name }}</span>
                            </label>
                        @empty
                            <p class="text-muted" id="no-tags-message">No tags have been created for this division yet.</p>
                        @endforelse
                    </div>
                    <div class="panel-footer">
                        <label class="control-label m-b-sm" style="display: block;">Create New Tag</label>
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" id="new-tag-name" class="form-control input-sm" placeholder="Tag name">
                            <span class="input-group-addon" style="padding: 0; border: none; width: auto;">
                                <select id="new-tag-visibility" class="form-control input-sm" style="border-radius: 0;">
                                    <option value="public">Public</option>
                                    <option value="leadership">Leadership</option>
                                    @if($isSeniorLeader)
                                        <option value="senior_leader">Senior Leader</option>
                                    @endif
                                </select>
                            </span>
                            <span class="input-group-btn">
                                <button type="button" id="create-tag-btn" class="btn btn-default btn-sm">
                                    <i class="fa fa-plus"></i> Create
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('bulk-tags.store', $division) }}" method="POST" id="bulk-tags-form">
                    @csrf

                    @if (isset($returnTo))
                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    @endif

                    @foreach ($members as $member)
                        <input type="hidden" name="member_ids[]" value="{{ $member->id }}">
                    @endforeach

                    <div class="panel panel-filled panel-c-accent">
                        <div class="panel-heading">
                            Action
                        </div>
                        <div class="panel-body">
                            <label style="margin-right: 20px;">
                                <input type="radio" name="action" value="assign" checked>
                                Assign selected tags
                            </label>
                            <label>
                                <input type="radio" name="action" value="remove">
                                Remove selected tags
                            </label>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-accent">
                                <i class="fa fa-check"></i> Apply Changes
                            </button>
                            <a href="{{ $returnTo ?? route('division.members', $division) }}" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>

            </div>

            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        Selected Members
                        <span class="badge pull-right">{{ $members->count() }}</span>
                    </div>
                    <div class="panel-body" style="max-height: 400px; overflow-y: auto;">
                        @foreach ($members as $member)
                            <div class="m-b-xs">
                                <small class="text-muted">{{ $member->rank->getAbbreviation() }}</small>
                                {{ $member->name }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('footer_scripts')
<style>
.tag-checkbox {
    display: inline-block;
    margin: 4px 8px 4px 0;
    cursor: pointer;
}
.tag-checkbox input[type="checkbox"] {
    margin-right: 4px;
    vertical-align: middle;
}
.tag-checkbox .badge {
    vertical-align: middle;
}
</style>
<script>
(function($) {
    $('#create-tag-btn').on('click', function() {
        var tagName = $('#new-tag-name').val().trim();
        var tagVisibility = $('#new-tag-visibility').val();

        if (!tagName) {
            toastr.warning('Please enter a tag name');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('bulk-tags.create-tag', $division) }}',
            method: 'POST',
            data: {
                name: tagName,
                visibility: tagVisibility,
                _token: $('meta[name=csrf-token]').attr('content')
            },
            success: function(data) {
                var html = '<label class="tag-checkbox">' +
                    '<input type="checkbox" name="tags[]" value="' + data.tag.id + '" form="bulk-tags-form" checked>' +
                    '<span class="badge tag-visibility-' + data.tag.visibility + '">' + data.tag.name + '</span>' +
                    '</label>';
                $('#no-tags-message').hide();
                $('#tags-list').append(html);
                $('#new-tag-name').val('');
                toastr.success('Tag "' + data.tag.name + '" created');
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Failed to create tag';
                toastr.error(message);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    $('#new-tag-name').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#create-tag-btn').click();
        }
    });
})(jQuery);
</script>
@endsection
