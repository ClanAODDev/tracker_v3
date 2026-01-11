@if($award->canBeRequestedBy())
    <div class="modal fade" id="award_modal" tabindex="-1" role="dialog" aria-labelledby="award_modal_title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="award_modal_title">Request Award</h4>
                </div>
                <form action="{{ route('awards.store-recommendation', $award) }}" method="post" id="award-request-form">
                    @csrf
                    <div class="modal-body">
                        @include('application.partials.errors')

                        <div class="award-request-header">
                            <img src="{{ $award->getImagePath() }}" class="clan-award" alt="{{ $award->name }}">
                            <div class="award-request-info">
                                <h5>{{ $award->name }}</h5>
                                <p class="text-muted">{{ $award->description }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group{{ $errors->has('member') ? ' has-error' : '' }}">
                            <label for="member">Recipient</label>
                            <div class="input-group">
                                <input type="text" class="form-control search-member" name="member" id="member"
                                       autocomplete="off" placeholder="Search for a member..."
                                       value="{{ old('member') }}" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="request-for-self" title="Request for myself">
                                        <i class="fa fa-user"></i> Me
                                    </button>
                                </span>
                            </div>
                            <input type="hidden" name="member_id" id="member_id" value="{{ old('member_id') }}">
                        </div>

                        <div class="form-group{{ $errors->has('reason') ? ' has-error' : '' }}">
                            <label for="reason">Justification</label>
                            <textarea name="reason" id="reason" rows="4" required
                                      class="form-control" placeholder="Explain why this award should be granted...">{{ old('reason') }}</textarea>
                            @if ($award->instructions)
                                <p class="help-block"><i class="fa fa-info-circle text-info"></i> {{ $award->instructions }}</p>
                            @endif
                        </div>

                        <div class="alert alert-info" style="margin-bottom: 0;">
                            <i class="fa fa-info-circle"></i>
                            Ensure all award criteria are met before requesting. You will be notified via Discord if your request is denied.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent" id="award-submit-btn">
                            <i class="fa fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
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