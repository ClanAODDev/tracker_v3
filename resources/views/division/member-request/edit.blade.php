@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="text-uppercase">Member Requests</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member-requests', $division) !!}

        <h3>Manage Member Request</h3>

        <p>Update the request with the appropriate changes. Invalid member ids must be reported to MSGT+.</p>

        <hr />

        <div class="panel panel-filled">
            <div class="panel-body">

                <form action="{{ route('division.member-requests.update', [$division, $memberRequest]) }}"
                      method="post">
                    {{ method_field('patch') }}
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Forum Name (without AOD_)</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="{{ $memberRequest->member->name }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="division">Division</label>
                                <select name="division" id="division" class="form-control">
                                    @foreach (\App\Division::active()->get() as $division)
                                        <option value="{{ $division->id }}"
                                                {{ $memberRequest->division_id === $division->id ? 'selected' : null }}>{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                <h3>Additional Details</h3>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="member_id"> Member Id </label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $memberRequest->member_id }}"
                                       disabled>
                                <span class="input-group-btn">
                            <a href="{{ doForumFunction([$memberRequest->member_id], 'forumProfile') }}" target="_blank"
                               class="btn btn-default" type="button">Validate</a>
                        </span>
                            </div><!-- /input-group -->
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="submitter">Original requester</label>
                            <input type="text" class="form-control" disabled
                                   value="{{ $memberRequest->requester->name }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label for="date">Requested on</label>
                        <input type="text" class="form-control"
                               value="{{ $memberRequest->created_at->format('Y-m-d') }}"
                               disabled>
                    </div>
                </div>

                @if ($memberRequest->notes)
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="canceller">Cancelled by</label>
                                <input type="text" class="form-control" value="{{ $memberRequest->canceller->name }}"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="notes">Reason for cancellation</label>
                                <textarea name="notes" id="notes" rows="3" disabled
                                          style="resize: none; overflow:scroll"
                                          class="form-control">{{ $memberRequest->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="text-left">
                    <form action="{{ route('division.member-requests.delete', [$division, $memberRequest]) }}" method="post">
                        {{ csrf_field() }}
                        {{ method_field('delete') }}
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">

                <div class="text-right">
                    <a href="{{ route('division.member-requests.index', $division) }}"
                       class="btn btn-default">Cancel</a>
                    <button class="btn btn-success" type="submit">Submit Request</button>
                </div>
            </div>
        </div>

    </div>

@stop
