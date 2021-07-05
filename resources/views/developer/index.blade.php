@extends('application.base-tracker')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Developers
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Developers
        @endslot
    @endcomponent

    <div class="container-fluid">
        <p>AOD provides several consumable APIs which are intended solely for internal community use. AOD data,
            tokens, and other resources including source code are not to be shared outside the AOD community, nor used
            for any other purpose. Any questions regarding the usage of AOD data or API tokens should be directed to
            clan leadership.</p>

        <p><strong class="text-danger">Misuse of these resources will result in revocation of access, and could lead to
                permanent removal from the clan. Don't do it.</strong></p>

        <hr>

        <h4>Personal Access Tokens</h4>

        <div class="col-md-12">

            @if($tokens->count())


                <ul class="list-group">
                    @foreach ($tokens as $token)
                        <li class="list-group-item row">
                            <form action="{{ route('developer.token.delete') }}" method="POST">
                                <div class="col-md-6">
                                    <p><strong>{{ $token->name }}</strong></p>
                                    <p class="text-muted">Last
                                        used: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}</p>
                                </div>
                                <div class="col-md-6">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                    <input type="hidden" value="{{ $token->id }}" name="token_id">
                                    <button type="submit" class="btn btn-danger pull-right m-t-sm"><i
                                            class="fa fa-trash text-danger"></i></button>
                                </div>

                            </form>
                        </li>
                    @endforeach
                </ul>

            @else
                <p class="text-muted">You do not currently have any tokens.</p>
            @endif

        </div>


        <hr>


        <form action="{{ route('developer.token.store') }}" method="POST">

            {{ csrf_field() }}

            <h4>Generate token</h4>

            <div class="row">

                <div class="form-group col-md-4">

                    <input type="text" class="form-control " name="token_name" id="token_name"
                           placeholder="My API token"
                           required/>
                </div>

                <div class="form-group">
                    <button class="btn btn-info">
                        <i class="text-info fa fa-key"></i> Create token
                    </button>
                </div>

            </div>


            @include('application.partials.errors')

        </form>

        @if(Session::has('token'))
            <code>{{ Session::get('token') }}</code>
        @endif


    </div>


@endsection
