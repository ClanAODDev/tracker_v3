<h4 class="m-t-xl">Member History</h4>
<hr>

<div class="tabs-container">

    <div class="tabs-top">
        <ul class="nav nav-tabs">
            <li class="active"><a class="nav-link active show" data-toggle="tab" href="#rank-tab"
                                  aria-expanded="true"><i class="fa fa-trophy text-accent"></i> Rank</a>
            </li>
            <li><a class="nav-link" data-toggle="tab" href="#division-tab" aria-expanded="false"><i class="fa
            fa-gamepad text-accent"></i>
                    Division</a></li>
            <li><a class="nav-link" data-toggle="tab" href="#recruiting-tab" aria-expanded="false"><i class="fa
            fa-user-plus text-accent"></i> Recruiting</a></li>
        </ul>
        <div class="tab-content">
            <div id="rank-tab" class="tab-pane active">
                <div class="panel-body">

                    @if ($rankHistory->count() > 0)

                        <table class="table">
                            <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Date Changed</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($rankHistory as $entry)
                                <tr>
                                    <td>{{ $entry->rank->name }}</td>
                                    <td>{{ $entry->created_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    @else
                        <p class="text-muted m-t-sm">This member currently has no rank history.</p>
                    @endif
                </div>
            </div>
            <div id="division-tab" class="tab-pane">
                <div class="panel-body">

                    @if ($transfers->count() > 0)

                        <table class="table">
                            <thead>
                            <tr>
                                <th>Division</th>
                                <th>Date Assigned</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($transfers as $transfer)
                                <tr>
                                    <td>{{ $transfer->division->name }}</td>
                                    <td>{{ $transfer->created_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    @else
                        <p class="text-muted m-t-sm">This member currently has no division assignment history.</p>
                    @endif

                </div>
            </div>
            <div id="recruiting-tab" class="tab-pane">

                <div class="panel-body">


                    @if (count($member->recruits))

                        <table class="table basic-datatable">
                            <thead>
                            <tr>
                                <th>Member</th>
                                <th>Join Date</th>
                                <th>Primary Division</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($member->recruits as $recruit)
                                <tr>
                                    <td>
                                        {{ $recruit->present()->rankName }}
                                        <span class="pull-right">
                    <a href="{{ route('member', $recruit->getUrlParams()) }}">
                        <i class="fa fa-search"></i>
                    </a>
                </span>
                                    </td>
                                    <td>{{ $recruit->join_date }}</td>
                                    <td>
                                        {{ $recruit->division->name ?? "Ex-AOD" }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>