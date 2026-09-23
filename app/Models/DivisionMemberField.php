<?php

namespace App\Models;

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
        'display_order',
        'filterable',
    ];

    protected $casts = [
        'type'          => DivisionMemberFieldType::class,
        'options'       => 'array',
        'display_order' => 'integer',
        'filterable'    => 'boolean',
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
        return $this->options ?? [];
    }
}
