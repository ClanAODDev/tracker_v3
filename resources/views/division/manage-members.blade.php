@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->abbreviation) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            </a>
        @endslot
        @slot ('heading')
            {{ $platoon->name }}
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

        <h4><i class="fa fa-cubes"></i> Manage Squad Assignments</h4>
        <p>Drag members between squads to assign them. Only squad members will be shown; squad leaders cannot be reassigned from this view.</p>
        <p>If you wish to reassign an entire squad to a new platoon, you can perform that function from the
            <code>Edit Squad</code> view. </p>
        <p>To more easily access manipulate members, you can drag squads to reorder them</p>

        <div class="m-t-xl">
            <a href="{{ route('platoon', [$division->abbreviation, $platoon]) }}" class="btn btn-default">Cancel</a>
            <a class="btn btn-success"
               href="{{ route('createSquad', [$division->abbreviation, $platoon]) }}">
                <i class="fa fa-plus"></i> Create Squad
            </a>
        </div>

        <hr />

        <div class="m-t-xl">

            <div class="row mod-plt sortable-squad">
                @foreach ($platoon->squads as $squad)
                    <div class="col-md-3">
                        <h5 class="grabbable"><i class="fa fa-drag-handle text-muted"></i>
                            <strong>{{ $squad->name or "Untitled" }}</strong>
                            @if ($squad->leader)
                                <small>{{ $squad->leader->present()->rankName }}</small>
                            @else
                                <small>TBA</small>
                            @endif
                            <span class="pull-right badge badge-default count">{{ count($squad->members) }}</span>
                        </h5>
                        <hr />
                        <ul class="sortable" data-squad-id="{{ $squad->id }}" style="min-height: 50px;">
                            @foreach ($squad->members as $member)
                                <li class="list-group-item grabbable" data-member-id="{{ $member->id }}">
                                    <i class="fa fa-drag-handle text-muted pull-right"></i>
                                    <span class="no-select">{{ $member->present()->rankName }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
      $('.sortable-squad').sortable()

      $('.draggable').draggable({
        connectToSortable: 'ul',
        revert: 'invalid',
        scroll: true,
        scrollSensitivity: 100
      })

      var itemMoved, targetSquad, senderLength, receiverLength
      $('.mod-plt .sortable').sortable({
        connectWith: 'ul',
        placeholder: 'ui-state-highlight',
        receive: function (event, ui) {
          itemMoved = $(ui.item).attr('data-member-id')
          targetSquad = $(this).attr('data-squad-id')
          senderLength = $(ui.sender).find('li').length
          receiverLength = $(this).find('li').length
          if (undefined === targetSquad) {
            alert('You cannot move players to this list')
            $('.mod-plt .sortable').sortable('cancel')
          } else {
            // is genpop empty?
            if ($('.genpop').find('li').length < 1) {
              $('.genpop').fadeOut()
            }
            // update squad counts
            $(ui.sender).parent().find('.count').text(senderLength)
            $(this).parent().find('.count').text(receiverLength)
            $.ajax({
              type: 'POST',
              url: window.Laravel.appPath + '/members/assign-squad',
              data: {
                member_id: itemMoved,
                squad_id: targetSquad,
                _token: $('meta[name=csrf-token]').attr('content')
              },
              dataType: 'json',
              success: function (response) {
                console.log(response)
                toastr.success('Member reassigned!')
              },
              error: function (response) {
                console.log(response)
              }
            })
          }
        }
      })

    </script>

@stop
