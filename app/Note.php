<?php

namespace App;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use RecordsActivity;
    use SoftDeletes;

    protected static $recordEvents = [
        'created',
        'updated',
        'deleted'
    ];

    protected static $noteTypes = [
        'negative' => 'Negative',
        'positive' => 'Positive',
        'misc' => 'Misc',
    ];

    protected $fillable = [
        'type',
        'body',
        'forum_thread_id'
    ];

    protected $with = [
        'tags',
        'member'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'clan_id');
    }

    /**
     * Get tags for a note
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get tag ids for this note
     *
     * @return array
     */
    public function getTagListAttribute()
    {
        return $this->tags->pluck('id')->all();
    }
}
