@extends('application.base')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h4>Bug Reports</h4>
                <p>If you encounter issues or problems while using the tracker, please report them here. In your report, please be as specific as possible about the details of the error. Ex. the steps you took before the error, any odd behavior you noticed, the URL, etc.</p>

                <p>You can also report feature / change requests through this form.</p>

                <p>Please review existing reports to ensure you are not creating a duplicate.</p>
                <hr />

                @forelse ($issues as $issue)
                    <div class="panel panel-filled">
                        <div class="panel-heading">
                            {{ $issue['title'] }}
                            <span class="label text-uppercase {{ ($issue['state'] === 'open') ? 'label-success' : 'label-warning' }}">
                                    {{ $issue['state'] }}
                                </span>
                            <span class="text-muted pull-right">Issue #{{ $issue['number'] }}</span>
                        </div>
                        <div class="panel-body">
                            {{ $issue['body'] }}
                        </div>
                    </div>
                @empty
                    <div class="panel panel-filled">
                        <div class="panel-heading">No issues</div>
                        <div class="panel-body">
                            There are no reported issues open or being resolved.
                        </div>
                    </div>
                @endforelse

                <div class="panel panel-filled panel-c-info collapsed">
                    <div class="panel-heading panel-toggle">
                        Report an issue
                        <div class="panel-tools">
                            <i class="fa fa-chevron-up toggle-icon"></i>
                        </div>
                    </div>
                    <div class="panel-body">
                        <form action="{{ route('github.create-issue') }}" id="create-issue" method="post">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label for="labels">Issue Type</label>
                                <select name="labels" id="labels" class="form-control">
                                    <option value="bug">Bug / Error</option>
                                    <option value="feature request">Feature Request</option>
                                    <option value="question">Question</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="title">Issue Title</label>
                                <input type="text" class="form-control" name="title" required />
                            </div>
                            <div class="form-group">
                                <label for="body">Issue Details</label>
                                <textarea name="body" id="body" class="form-control" rows="5"
                                          style="resize: vertical;" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop