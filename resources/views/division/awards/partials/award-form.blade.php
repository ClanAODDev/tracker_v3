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

                        <div class="form-group">

                            <label for="member">Member receiving award*</label>
                            <input type="text" class="form-control search-member" name="member" id="member"
                                   autocomplete="off" placeholder="Search"
                            />
                            <i class="fa fa-search pull-right" style="position: absolute; right: 20px; top: 35px;"></i>
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