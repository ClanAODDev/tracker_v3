<div class="table-responsive">
    <div class="panel panel-filled">

        <table class="table table-hover basic-datatable">

            <thead>
            <tr>
                <th class="no-sort"></th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Dev Enabled</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td class="text-center">
                        <a title="View Profile" class="btn btn-default"
                           href="{{ route('member', $user->member->clan_id) }}"><i
                                    class="fa fa-user"></i>
                        </a>
                    </td>
                    <td>{{ $user->member->present()->rankName }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role->label }}</td>
                    <td class="text-uppercase text-center">
                        <span class="text-{{ ($user->isDeveloper()) ? 'danger' : 'muted' }}">
                            {{ var_export($user->isDeveloper()) }}
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

