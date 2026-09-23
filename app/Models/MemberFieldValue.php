<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'division_member_field_id',
        'value',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(DivisionMemberField::class, 'division_member_field_id');
    }
}
