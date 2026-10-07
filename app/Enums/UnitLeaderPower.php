<?php

namespace App\Enums;

use App\Models\Division;
use App\Models\DivisionUnitLevel;

enum UnitLeaderPower: string
{
    case SeeRankActions         = 'see_rank_actions';
    case RequestPromotions      = 'request_promotions';
    case ApprovePromotions      = 'approve_promotions';
    case CommentOnRankActions   = 'comment_on_rank_actions';
    case ManageUnit             = 'manage_unit';
    case ManageHandlesAndFields = 'manage_handles_and_fields';

    public function tiers(): array
    {
        return match ($this) {
            self::RequestPromotions, self::ManageHandlesAndFields => [UnitLevel::Platoon, UnitLevel::Squad],
            default                                               => [UnitLevel::Platoon],
        };
    }

    public function appliesTo(UnitLevel $tier): bool
    {
        return in_array($tier, $this->tiers(), true);
    }

    public function describe(UnitLevel $tier, Division $division, int $depth): ?string
    {
        if (! $this->appliesTo($tier)) {
            return null;
        }

        $levels   = $division->unitLevels->keyBy('depth');
        $unit     = strtolower($levels->get($depth)?->label ?? 'unit');
        $below    = $levels->filter(fn (DivisionUnitLevel $level) => $level->depth > $depth);
        $children = $below->isEmpty() ? null : strtolower($below->sortBy('depth')->first()->label_plural);
        $subtree  = $children ? "their {$unit} and every {$this->singular($below)} under it" : "their {$unit}";
        $approve  = Rank::from($division->settings()->get('max_platoon_leader_rank'))->getLabel();
        $request  = config($tier === UnitLevel::Platoon ? 'aod.rank.max_platoon_leader' : 'aod.rank.max_squad_leader')->getLabel();

        return match ($this) {
            self::SeeRankActions       => "See rank actions for members of {$subtree} ranked below themselves",
            self::RequestPromotions    => "Request promotions for members of {$subtree} ranked below {$request}",
            self::ApprovePromotions    => "Approve promotions in {$subtree} up to {$approve}",
            self::CommentOnRankActions => "Comment on rank actions in {$subtree} up to {$approve}",
            self::ManageUnit           => $children
                ? "Edit their {$unit} and its {$children}, delete {$children}, and move members between them"
                : "Edit their {$unit}",
            self::ManageHandlesAndFields => "Edit in-game handles and division fields for members of {$subtree}",
        };
    }

    private function singular($levels): string
    {
        return strtolower($levels->sortBy('depth')->first()->label);
    }
}
