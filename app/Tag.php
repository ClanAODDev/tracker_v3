<?php

namespace App;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    use RecordsActivity;

    protected static $recordEvents = [
        'created',
        'deleted'
    ];

    protected $touches = [
        'notes'
    ];

    protected $fillable = ['name'];

    /**
     * Get all notes with a particular tag
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes()
    {
        return $this->belongsToMany(Note::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

}
