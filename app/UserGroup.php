<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $connection = 'aod_forums';
    protected $table = 'forum_usergroups';
    protected $primaryKey = 'usergroupid';
}
