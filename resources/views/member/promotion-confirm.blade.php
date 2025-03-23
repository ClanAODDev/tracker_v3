@extends('application.base-tracker')

@section('content')

    <div class="container-center md">

        <div class="container-center md">

            <div class="view-header">
                <div class="header-icon">
                    <i class="pe page-header-icon pe-7s-add-user"></i>
                </div>
                <div class="header-title mb-3">
                    <h3>Congratulations</h3>
                    <small>
                        Promotion pending for {{ $member->name }}
                    </small>
                </div>
            </div>

            <div class="panel panel-filled m-t-md">
                <div class="panel-body m-b-n p-0">

                    @if (request()->routeIs('promotion.accept'))
                        <p>Your promotion has been accepted, and will reflect on the forums and discord</p>
                        <p>Congratulations</p>
                        <p>You may close this window</p>
                    @else
                        <p>Your promotion has been declined. No change will be made, but a record will be kept
                            should you change your mind in the future.</p>
                        <p>You may close this window</p>
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection

