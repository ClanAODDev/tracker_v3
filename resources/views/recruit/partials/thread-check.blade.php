<div class="table-responsive">
    <table class="table table-hover">

        @foreach ($threads as $thread)
            <tr>
                <td>{{ $thread['thread_name'] }}</td>
                <td>
                    <button class="btn btn-default copy-to-clipboard" type="button"
                            data-clipboard-text="{{ doForumFunction([$thread['thread_id']], 'showThread') }}">
                        <i class="fa fa-clipboard"></i> Copy Link
                    </button>
                </td>
                <td>
                    @if ($thread['status'])
                        <i class="fa text-success fa-2x fa-check-circle"></i>
                    @else
                        <i class="fa text-danger fa-2x fa-times-circle"></i>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>

</script>