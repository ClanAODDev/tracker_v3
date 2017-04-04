<div class="table-responsive">
    <div class="panel">

        <table class="table table-hover basic-datatable">

            <thead>
            <tr>
                <th class="no-sort"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Dev</th>
                <th>Last Logged In</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>
                        <a title="View Profile" class="btn btn-default"
                           href="{{ route('member', $user->member->clan_id) }}"><i
                                    class="fa fa-user"></i>
                        </a>
                    </td>
                    <td>
                        {{ $user->member->name }}
                        <small class="slight text-muted">{{ $user->member->rank->abbreviation }}</small>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        {{ $user->role->id }}
                        <small class="slight text-muted">{{ $user->role->label }}</small>
                    </td>
                    <td class="text-uppercase">
                        <small class="slight text-{{ ($user->isDeveloper()) ? 'success' : 'muted' }}">
                            {{ var_export($user->isDeveloper()) }}
                        </small>
                    </td>
                    <td>{{ null !== $user->last_login_at ? $user->last_login_at->diffForHumans() : "Never" }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

