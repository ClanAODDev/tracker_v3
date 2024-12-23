@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset(config('app.logo')) }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div style="display:flex;align-items: center;justify-content: space-around;margin-top: 0;">
            <img src="{{ asset(Storage::url($award->image)) }}"
                 class="clan-award"
                 alt="{{ $award->name }}"
            />

            <div class="hidden-xs hidden-sm text-center">
                <h3>{{ $award->name }}</h3>
                <p style="max-width:500px;">{{ $award->description }}</p>
            </div>

            <div>
                <a href="#" data-toggle="modal" data-target="#award_modal"
                   {{ $award->allow_request ? null : "disabled" }}
                   title="Request this award for yourself or someone else"
                   class="btn btn-default {{ $award->allow_request ? null : "disabled" }}">Request Award</a>
            </div>

        </div>
    </div>

    <div class="visible-xs visible-sm text-center">
        <hr>
        <h3>{{ $award->name }}</h3>
        <p>{{ $award->description }}</p>

    </div>

    <hr>

    <h4>Award Recipients</h4>


    <table class="table table-hover basic-datatable">
        <thead>
        <tr>
            <th>Member</th>
            <th>Awarded on</th>
        </tr>
        </thead>
        @foreach ($award->recipients as $record)
            <tr>
                <td><a href="{{ route('member', $record->member->getUrlParams()) }}">
                        {{ $record->member->name }}
                    </a></td>
                <td>{{ $record->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </table>

    @if($award->allow_request)
        <div class="modal fade" id="award_modal">
            <div class="modal-dialog" role="document" style="background-color: #000;">
                @include('application.partials.errors')
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        Request award: {{ $award->name }}
                    </div>
                    <div class="panel-body">
                        <p>Please ensure all award criteria are met before recommending a member for this award.</p>
                        <p><strong class="c-accent">Award description:</strong> {{ $award->description }}</p>
                        <form action="{{ route('awards.store-recommendation', $award) }}" method="post">
                            @csrf
                            <div class="form-group {{ $errors->has('reason') ? ' has-error' : null }}">
                                <label for="reason">Justification*</label>
                                <textarea name="reason" id="reason" rows="4" required
                                          class="form-control">{{ old('reason') }}</textarea>
                            </div>

                            <div class="form-group {{ $errors->has('member_id') ? ' has-error' : null }}">
                                <label for="member_id">Member ID*</label>
                                <input type="number" name="member_id" id="member_id" class="form-control"
                                       value="{{ old('member_id') }}" required>
                            </div>

                            <button type="submit" class="btn-default btn pull-right">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('#award_modal').modal('show');
            });
        </script>
    @endif

@endsection
