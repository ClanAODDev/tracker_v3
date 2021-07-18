@include('application.partials.errors')

<div id="selected-data" class="panel panel-c-info" style="display:none;">
    <div class="panel-body">
        <h4 class="status-text"></h4>
        <div class="actions">
            {{--<a href="#" class="btn btn-default disabled">Move...</a>--}}
            <form action="{{ route('private-message.create', compact('division')) }}" method="POST">
                <input type="hidden" id="pm-member-data" name="pm-member-data">
                {{ csrf_field() }}
                <button type="submit" href="#" class="btn btn-default">
                    <i class="fa fa-bullhorn text-accent"></i> <span
                            class="hidden-xs hidden-sm">Send PM</span>...
                </button>
            </form>
        </div>
    </div>
</div>