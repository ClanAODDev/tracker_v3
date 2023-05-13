<?php

namespace App\Http\Requests;

use App\Models\Rank;
use App\Notifications\MemberRecommendationSubmitted;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecommendationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('recommend', request()->member);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'rank_id' => 'required|integer',
            'justification' => 'required',
            'effective_at' => 'required|date',
            'member_id' => [
                'required',
                Rule::exists('recommendations')->where($this->noFutureRecommendationsExist())
            ]
        ];
    }

    public function messages()
    {
        return [
            'justification.required' => 'Please provide a justification for your recommendation',
            'member_id.exists' => 'That member already has a recommendation for the current or a future month'
        ];
    }

    public function persist()
    {
        $rank = Rank::find($this->rank_id);

        $recommendation = $rank->recommendation()->create(array_merge(request([
            'justification',
            'effective_at',
        ]), [
            'member_id' => request('member')->id,
            'admin_id' => auth()->user()->member_id,
            'division_id' => request()->member->division_id,
        ]));

        if ('on' === request()->member->division->settings()->get('slack_member_notification_created')) {
            request()->member->division->notify(
                new MemberRecommendationSubmitted($recommendation)
            );
        }
    }

    /**
     * @return \Closure
     */
    private function noFutureRecommendationsExist(): \Closure
    {

        return function (Builder $query) {

            return (
                // is there a future recommendation
                $query->where('effective_at', '>', now())->exists()

                OR

                // is there a recommendation for the current month and year
                $query->whereMonth('effective_at', now()->format('m'))
                    ->whereYear('effective_at', now()->format('Y'))->exists()
            );
        };
    }
}
