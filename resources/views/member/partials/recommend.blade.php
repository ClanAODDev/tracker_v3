<div class="modal fade" id="create-recommendation">
    <div class="modal-dialog" role="document" style="background-color: #000;">
        {!! Form::model(App\Models\Recommendation::class, ['method' => 'put', 'route' => ['member.recommendations.store', $member->clan_id]]) !!}
        @include('member.forms.recommendation-form')
        <input type="hidden" name="recommend-form" value="true">
        {!! Form::close() !!}
    </div>
</div>

@if ($errors->count() && old('recommend-form'))
    <script>$('#create-recommendation').modal();</script>
@endif