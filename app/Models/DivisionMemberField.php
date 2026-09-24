<?php

namespace App\Models;

use App\Enums\DivisionMemberFieldColor;
use App\Enums\DivisionMemberFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DivisionMemberField extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
        'key',
        'label',
        'type',
        'options',
        'filterable',
        'self_editable',
    ];

    protected $casts = [
        'type'          => DivisionMemberFieldType::class,
        'options'       => 'array',
        'filterable'    => 'boolean',
        'self_editable' => 'boolean',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(MemberFieldValue::class);
    }

    /**
     * @return array<int, string>
     */
    public function optionList(): array
    {
        return array_column($this->options ?? [], 'value');
    }

    /**
     * @return array<string, string>
     */
    public function optionColors(): array
    {
        return collect($this->options ?? [])
            ->filter(fn (mixed $option) => is_array($option) && isset($option['value']))
            ->mapWithKeys(fn (array $option) => [$option['value'] => $option['color'] ?? DivisionMemberFieldColor::GRAY->value])
            ->all();
    }
}
