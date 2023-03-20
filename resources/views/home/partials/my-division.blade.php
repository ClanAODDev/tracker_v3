<div class="panel panel-c-accent panel-filled division-header"
     style="background-image: url({{ asset('images/headers/' . $myDivision->abbreviation . ".png?") }})">
    <div class="panel-heading">

        <style>
            .my-division h2 a {
                color: #fff;
            }

            .my-division h2 a:hover {
                color: #629418;
                text-decoration: none;
            }
        </style>

        <h2 class="m-b-none text-uppercase my-division">
            <a href="{{ route('division', $myDivision->abbreviation) }}">{{ $myDivision->name }}</a>
            @include('division.partials.edit-division-button', ['division' => $myDivision])
        </h2>

        <span class="c-text">{{ $myDivision->members->count() }} MEMBERS</span>
    </div>

    <div class="panel-body">
        @include('division.partials.tools-links', ['division' => $myDivision])
    </div>
</div>

@include('division.partials.notices', ['division' => $myDivision])
