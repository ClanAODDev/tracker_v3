@extends('application.base')
@section('content')

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
            Manage Slack Files
        @endslot
    @endcomponent

    <div class="container-fluid">

        <p class="alert alert-warning">Note: List does not include files shared in private conversations or channels. Purging is capped at 100 files per request, so use with caution.</p>

        <div class="panel panel-filled">
            <div class="panel-heading">
                Storage Usage
                <a href="{{ route('slack.files.purge') }}" class="btn btn-danger pull-right">
                    <i class="fa fa-trash"></i> Mass Purge
                </a>
            </div>
            <div class="panel-body">
                <h4>{{ $percentUsage }}% <small>{{ bytesToHuman($storage) }}</small></h4>
                <div class="progress">
                    <div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width: {{ $percentUsage }}%" aria-valuenow="{{ $percentUsage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <div class="row m-t-lg">
            <div class="col-md-12">

                <div class="panel panel-filled">
                    <div class="panel-heading">All Files</div>
                    <div class="panel-body">
                        @if (count($files))

                            <table class="table table-hover basic-datatable">
                                <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Date Uploaded</th>
                                    <th>Delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($files as $file)
                                    <tr>
                                        <td>
                                            <a target="_blank" href="{{ $file['permalink'] }}">{{ $file['name'] }}</a>
                                            <span class="text-muted">{{ bytesToHuman($file['size']) }}</span>
                                        </td>
                                        <td>
                                            {{ $file['size'] }}
                                        </td>
                                        <td>{{ Carbon::createFromTimestamp($file['created'])->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('slack.files.delete', $file['id']) }}"
                                               class="btn btn-danger">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                    </div>
                    @else
                        <p>No files found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop