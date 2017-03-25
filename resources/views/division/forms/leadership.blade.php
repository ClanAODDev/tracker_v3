<table class="table table-hover basic-datatable">
    <thead>
    <tr>
        <th></th>
        <th>Member</th>
        <th>Position</th>
        <th>User Role</th>
        <th>Last Login</th>
    </tr>
    </thead>

    <tbody>
    @forelse($leaders as $leader)
        <tr>
            <td>
                <a title="View Profile" class="btn btn-default"
                   href="{{ route('member', $leader->clan_id) }}"><i
                            class="fa fa-user"></i>
                </a>
            </td>
            <td>{{ $leader->name }}</td>
            <td>{{ $leader->position->name }}</td>
            <td>
                @if ($leader->user)
                    {{ $leader->user->role->label }}
                @else
                    <span class="text-muted text-uppercase slight">No account</span>
                @endif
            </td>
            <td>{{ $leader->created_at }}</td>
        </tr>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-danger">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No leadership assigned
                    </h4>
                    <small class="slight">A clan administrator must update this division in order to assign leadership</small>
                </div>
            </div>
        </div>
    @endforelse
    </tbody>
</table>