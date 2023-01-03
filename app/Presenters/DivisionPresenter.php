<?php

namespace App\Presenters;

use App\Models\Division;

class DivisionPresenter extends Presenter
{
    /**
     * @var Division
     */
    protected $division;

    private $threadLink = 'http://www.clanaod.net/forums/showthread.php?t=';

    private $forumLink = 'http://www.clanaod.net/forums/forumdisplay.php?f=';

    public function __construct(Division $division)
    {
        $this->division = $division;
    }

    /**
     * Get Division Structure.
     *
     * @return string
     */
    public function divisionStructureLink()
    {
        return $this->threadLink . $this->division->division_structure;
    }

    /**
     * Get welcome forum / thread attribute as a proper URL.
     *
     * @param $value
     * @return string
     */
    public function welcomeAreaLink()
    {
        if ($this->division->settings['useWelcomeThread']) {
            return $this->threadLink . $this->division->settings()->welcome_area;
        }

        // defaults to forum area
        return $this->forumLink . $this->division->settings()->welcome_area;
    }
}
