<div class="table-responsive">

    <table class="table table-hover basic-datatable">
        <thead>
        <tr>
            <th class="no-sort"></th>
            <th>Division</th>
            <th class="no-sort text-center">Icon</th>
            <th class="text-center text-center">Status</th>
            <th class="no-sort text-center">Abbr</th>
            <th class="no-sort">Desc</th>
        </tr>
        </thead>

        <tbody>
        @foreach($divisions as $division)
            <tr>
                <td>
                    <a title="Edit Division" class="btn btn-default"
                       href="{{ route('adminEditDivision', $division->abbreviation) }}"><i
                                class="fa fa-wrench"></i>
                    </a>
                </td>

                <td>
                    {{ $division->name }}
                </td>

                <td class="text-center">
                    @if (hasDivisionIcon($division->abbreviation))
                        <img class="division-icon-medium"
                             src="{{ getDivisionIconPath($division->abbreviation) }}"
                        />
                    @else
                        <img class="division-icon-medium"
                             src="{{ asset('images/logo_v2.png') }}"
                        />
                    @endif
                </td>

                <td class="text-center">
                    @if($division->active)
                        <span class="slight text-success">ACTIVE</span>
                    @else
                        <span class="slight text-muted">INACTIVE</span>
                    @endif
                </td>

                <td class="text-center">
                    <code>{{ $division->abbreviation }}</code>
                </td>

                <td class="col-xs-5">
                    {{ $division->description }}
                </td>

            </tr>
        @endforeach
        </tbody>

    </table>
</div>