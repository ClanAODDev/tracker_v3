<?php

namespace App\Enums;

use App\Models\Division;
use App\Models\DivisionUnitLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    public static function forLevel(Collection $levels, int $depth, Rank $approveLimit): array
    {
        $tier = UnitLevel::forDepth($depth, (int) $levels->max('depth'));

        return collect(self::cases())
            ->map(fn (self $power) => $power->describe($tier, $levels, $depth, $approveLimit))
            ->filter()
            ->values()
            ->all();
    }

    public static function forDivision(Division $division, int $depth): array
    {
        return self::forLevel(
            $division->unitLevels->map(fn (DivisionUnitLevel $level) => $level->only('depth', 'label', 'label_plural')),
            $depth,
            Rank::from($division->settings()->get('max_platoon_leader_rank')),
        );
    }

    public function describe(UnitLevel $tier, Collection $levels, int $depth, Rank $approveLimit): ?string
    {
        if (! $this->appliesTo($tier)) {
            return null;
        }

        $levels   = $levels->map(fn ($level) => (array) $level)->keyBy('depth');
        $unit     = Str::title($levels->get($depth)['label'] ?? 'unit');
        $below    = $levels->filter(fn (array $level) => $level['depth'] > $depth)->sortBy('depth');
        $children = $below->isEmpty() ? null : Str::title($below->first()['label_plural']);
        $subtree  = $children ? "their {$unit} and every " . Str::title($below->first()['label']) . ' under it' : "their {$unit}";
        $approve  = $approveLimit->getLabel();
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
}
