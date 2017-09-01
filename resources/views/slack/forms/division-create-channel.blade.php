{{ csrf_field() }}
<input type="hidden" name="division" id="division" value="{{ $division->abbreviation }}" />

@include('slack.forms.channel-name')

<button type="submit" class="btn btn-default">
    Create <span class="channel-name-output"></span>
</button>

<script>
  function updateChannelName () {
    $('.channel-name-output').text('#' + slugify($('#division').val() + '-' + $('input[name="channel-name"]').val()));
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