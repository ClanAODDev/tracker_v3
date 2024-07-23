<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="member">Search for member</label>
            <input type="text" class="form-control search-member"
                   name="member" id="member" autocomplete="off"/>
            <i class="fa fa-search pull-right"
               style="position: absolute; right: 20px; top: 35px;"></i>
        </div>
        <div class="form-group {{ $errors->has('end_date') ? ' has-error' : null }}">
            <label for="end_date">Leave End Date</label><span class="text-accent">*</span>
            <input type="date" class="form-control" required="required"
                   placeholder="mm/dd/yyyy" id="end_date" name="end_date">
        </div>
        <div class="form-group">
            <label for="note_thread_id">Forum Thread Id</label>
            <input type="number" name="note_thread_id" id="note_thread_id" class="form-control">
        </div>
    </div>
    <div class="col-md-8">

        <div class="row">
            <div class="col-xs-6">
                <div class="form-group {{ $errors->has('member_id') ? ' has-error' : null }}">
                    <label for="member_id">Member Id</label><span class="text-accent">*</span>
                    <input type="number" class="form-control" required="required" id="member_id" name="member_id">
                </div>
            </div>
            <div class="col-xs-6">
                <label for="leave_type">Leave Type</label><span class="text-accent">*</span>
                <select name="leave_type" id="leave_type" class="form-control">
                    @foreach (config('app.aod.leave_reasons') as $reason)
                        <option value="{{ strtolower($reason) }}">{{ $reason }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <label for="note_body">Justification</label> <span class="text-accent">*</span>
        <textarea name="note_body" id="note_body" rows="5" style="resize:vertical" required="required"
                  class="form-control"></textarea>
    </div>
</div>
<button class="btn btn-success pull-right m-t-sm form-group" type="submit">Submit</button>

<script>
    $('#leader').bootcomplete({
        url: window.Laravel.appPath + '/search-leader/',
        minLength: 3,
        idField: true,
        method: 'POST',
        dataParams: {_token: $('meta[name=csrf-token]').attr('content')}
    });
</script>