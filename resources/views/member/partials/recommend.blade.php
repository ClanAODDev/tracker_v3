<div class="modal fade" id="create-recommendation">
    <div class="modal-dialog" role="document" style="background-color: #000;">
        {!! Form::model(App\Models\Recommendation::class, ['method' => 'put', 'route' => ['member.recommendations.store', $member->clan_id]]) !!}
        @include('member.forms.recommendation-form', ['action' => 'Recommend member'])
        <input type="hidden" name="recommendation-form">
        {!! Form::close() !!}
    </div>
</div>

{{--@if ($errors->count() &&  && request('recommend-form'))--}}
    <script>$('#create-recommendation').modal();</script>
{{--@endif--}}