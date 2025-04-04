<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use RecordsActivity;
    use SoftDeletes;

    protected static $recordEvents = [
        'created',
        'updated',
        'deleted',
    ];

    protected static $noteTypes = [
        'misc' => 'Misc',
        'negative' => 'Negative',
        'positive' => 'Positive',
    ];

    protected $fillable = [
        'body',
        'member_id',
        'author_id',
        'type',
    ];

    /**
     * @return array
     */
    public static function allNoteTypes()
    {
        if (auth()->user()->role(['admin', 'sr_ldr'])) {
            static::$noteTypes['sr_ldr'] = 'Sr Leaders Only';
        }

        return static::$noteTypes;
    }

    /**
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function leave()
    {
        return $this->hasOne(Leave::class);
    }

    /**
     * Check to see if note has been changed.
     *
     * @return bool
     */
    public function changed()
    {
        return $this->updated_at !== $this->created_at;
    }
}
