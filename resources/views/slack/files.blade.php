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

        <h4>Manage Slack Files</h4>
        <hr />
        <p>Listed below are all files found for the Slack team. Keep in mind that this does not include files </p>

        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('slack.files.purge') }}" class="btn btn-danger"><i class="fa fa-trash"></i> Mass Purge</a>
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