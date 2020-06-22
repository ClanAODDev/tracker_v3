@if($note->type == 'negative')
    <i class="fa fa-thumbs-down text-danger"></i>
@elseif ($note->type == 'positive')
    <i class="fa fa-thumbs-up text-success"></i>
@elseif ($note->type == 'sr_ldr')
    <i class="fas fa-shield-alt"></i>
@else
    <i class="fa fa-comment text-accent"></i>
@endif