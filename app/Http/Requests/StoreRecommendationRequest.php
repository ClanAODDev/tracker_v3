<?php

namespace App\Http\Requests;

use App\Models\Rank;
use App\Models\Recommendation as Form;
use App\Notifications\MemberRecommendationSubmitted;
use Illuminate\Foundation\Http\FormRequest;

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
                $this->hasRecommendationForCurrentOrFutureMonth(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'justification.required' => 'Please provide a justification for your recommendation',
        ];
    }

    public function persist()
    {
        $rank = Rank::find($this->rank_id);

        $recommendation = $rank->recommendation()->create(array_merge(request([
            'justification',
            'effective_at',
        ]), [
            'member_id' => request('member_id'),
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
    private function hasRecommendationForCurrentOrFutureMonth(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $futureRecommendations = Form::whereMemberId($value)
                ->where(function ($query) {
                    $query
                        ->where('effective_at', '>', now())
                        ->orWhere(function ($query) {
                            $query->whereMonth('effective_at', now()->format('m'))
                                ->whereYear('effective_at', now()->format('Y'))->exists();
                        });
                })->count();

            if ($futureRecommendations) {
                $fail('That member already has a recommendation for the current or a future month');
            }
        };
    }
}
