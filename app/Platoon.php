<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Platoon extends Model
{

    /**
     * relationship - platoon belongs to a division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * relationship - platoon has many squads
     */
    public function squads()
    {
        return $this->hasMany(Squad::class);
    }

    /**
     * relationship - platoon has many members
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function leader()
    {
        return $this->hasOne(Member::class, 'clan_id', 'leader_id');
    }

    /**
     * Set forum activity attribute for platoon
     *
     * @return json
     */
    public function getForumActivityAttribute()
    {
        $twoWeeks = "last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -2 WEEK) AND CURDATE()";
        $oneMonth = "last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -30 DAY) AND DATE_ADD(CURDATE(), INTERVAL -2 WEEK)";
        $moreThanMonth = "last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -45 DAY) AND DATE_ADD(CURDATE(), INTERVAL -30 DAY)";
        $moreThan45Days = "last_forum_login < DATE_ADD(CURDATE(), INTERVAL -45 DAY)";

        $twoWeeksValue = $this->members()->whereRaw($twoWeeks)->count();
        $oneMonthValue = $this->members()->whereRaw($oneMonth)->count();
        $moreThanMonthValue = $this->members()->whereRaw($moreThanMonth)->count();
        $moreThan45DaysValue = $this->members()->whereRaw($moreThan45Days)->count();

        $data = [
            [
                'label' => '< 2 weeks ago',
                'color' => '#28b62c',
                'highlight' => '#5bc75e',
                'value' => $twoWeeksValue
            ],
            [
                'label' => '14 - 30 days ago',
                'color' => '#ff851b',
                'highlight' => '#ffa14f',
                'value' => $oneMonthValue
            ],
            [
                'label' => '30 - 45 days ago',
                'color' => '#ff4136',
                'highlight' => '#ff6c64',
                'value' => $moreThanMonthValue
            ],
            [
                'label' => '> 45 days ago',
                'color' => '#000',
                'highlight' => '#333',
                'value' => $moreThan45DaysValue
            ]
        ];

        return json_encode($data);
    }


}
