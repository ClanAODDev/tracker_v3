<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Connection('aod_forums')]
#[Table('forum_usergroups', key: 'usergroupid')]
class UserGroup extends Model {}
