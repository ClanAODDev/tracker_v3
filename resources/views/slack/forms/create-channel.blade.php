@include('application.partials.errors')

<div class="form-group">
    <label for="division">Division</label>
    <select name="division" id="division" class="form-control">
        @foreach (\App\Division::active()->get() as $division)
            <option value="{{ $division->abbreviation }}">{{ $division->name }}</option>
        @endforeach
    </select>
</div>
{{ csrf_field() }}

@include('slack.forms.channel-name')

<button type="submit" class="btn btn-default submit-button" disabled>
    Create <span class="channel-name-output"></span>
</button>

<script>
  function updateChannelName () {
    let division = $('#division').val(),
      channel = $('[name=channel-name]').val();
    $('.channel-name-output').text('#' + slugify(division + '-' + $('input[name="channel-name"]').val()));
    $('.submit-button').prop('disabled', (channel.length < 3));
  }

  function slugify (text) {
    return text.toString().toLowerCase().trim()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\s+/g, '-')
      .replace(/&/g, '')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-');
  }
</script>