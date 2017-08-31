{{ csrf_field() }}
<input type="hidden" name="division" id="division" value="{{ $division->abbreviation }}" />

<div class="form-group">
    <label for="name">Channel Name</label>
    <input type="text" name="channel-name" class="form-control" autocomplete="off"
           onkeyup="updateChannelName();" required />
</div>
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