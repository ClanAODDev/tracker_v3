<form action="{{ route('updateSquad', [$division->slug, $platoon, $squad]) }}" method="post">
    @method('put')
    @csrf
    <div class="row">
        <div class="col-sm-6">
            <div class="bs-example">
                <h4>{{ $division->locality('squad') }} Details</h4>
                <p>Please provide the details for your {{ $division->locality('squad') }}. Keep in mind the following
                    when
                    assigning a leader:</p>

                <p>Assigning a {{ $division->locality('squad leader') }}</p>
                <ul>
                    <li>
                        <span class="text-success">DOES</span> move them to this {{ $division->locality('squad') }}
                        {!! isset($squad) ? "<code>{$squad->name}</code>" : null !!}
                    </li>
                    <li><span class="text-success">DOES</span> change their position to
                        <code>{{ $division->locality('squad leader') }}</code></li>
                    <br/>
                    <li><span class="text-danger">DOES NOT</span> change user account access</li>
                </ul>
            </div>
        </div>

        <div class="col-sm-6">
            @include('application.partials.errors')
            <div class="panel panel-filled">
                <div class="panel-body">

                    <div class="form-group {{ $errors->has('name') ? ' has-error' : null }}">
                        <label for="name" class="form-label">Squad Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $squad->name }}">
                    </div>

                    <div class="form-group {{ $errors->has('logo') ? ' has-error' : null }}">
                        <label for="logo" class="form-label">{{ $division->locality('squad') }} Logo URL</label>
                        <input type="text" name="logo" id="logo" class="form-control" placeholder="https://"
                               value="{{ $squad->logo }}"
                        >
                    </div>

                    <div class="row">

                        <div class="col-xs-8">
                            <label for="leader">Search</label>
                            <input type="text" class="form-control" name="leader" id="leader" autocomplete="off"/>
                            <i class="fa fa-search pull-right" style="position: absolute; right: 20px; top: 35px;"></i>
                            <div class="form-group m-t-md">
                                <label for="is_tba">Leader TBA?</label>
                                <div style="margin-right:5px;float: left;">
                                    <input id="is_tba" name="is_tba"
                                           type="checkbox" {{ (empty($squad->leader_id)) ? "checked" : null }} />
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-4">
                            <div class="form-group {{ $errors->has('leader_id') ? ' has-error' : null }}">
                                <label for="leader_id">{{ $division->locality('squad leader') }}:</label>
                                <input type="number" name="leader_id" id="leader_id" class="form-control"
                                       value="{{ $squad->leader_id }}"
                                >
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('platoon.manage-squads', [$division->slug, $platoon]) }}"
                       class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-success pull-right">Save</button>
                </div>
            </div>
        </div>
    </div>

</form>



@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js?v=2') !!}"></script>
@endsection