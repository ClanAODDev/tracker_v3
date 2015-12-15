<?php

class MemberHandle extends Application
{
    public $id;
    public $member_id;
    public $handle_type;
    public $handle_value;
    public $handle_account_id;
    public $invalid;

    public static $id_field = 'id';
    public static $table = 'member_handles';

    public static function hasAlias($type, $member_id)
    {
        $handle_type = Handle::findByName($type);
        $params = self::find(array('handle_type' => $handle_type->id, 'member_id' => $member_id));
        return (count($params)) ? $params->id : false;
    }

    /**
     * fetches a member's handles and includes type information, visibility
     * @param  int $member_id the member's tracker member ID
     * @return object         returns handle information
     */
    public static function findByMemberId($member_id)
    {
        $params = self::find_each(array('member_id' => $member_id));
        foreach ($params as $memberHandleElement => $memberHandle) {
            $handle = Handle::findByType((int) $memberHandle->handle_type);
            if ($handle) {
                $memberHandle->handle_name = $handle->type;
                $memberHandle->name = $handle->name;
                $memberHandle->isInvalid = (bool) $memberHandle->invalid;
                $memberHandle->isVisible = (bool) $handle->show_on_profile;
                if (!is_null($handle->url)) {
                    $memberHandle->url = $handle->url;
                }
            } else {
                unset($params[$memberHandleElement]);
            }
        }
        return $params;
    }

    public static function findHandle($member_id, $type)
    {
        $params = self::find(array('member_id' => $member_id, 'handle_type' => $type));
        if (is_object($params)) {
            $handle = Handle::findByType($params->handle_type);
            $params->handle_name = $handle->type;
            $params->name = $handle->name;
            $params->isInvalid = (bool) $params->invalid;
            $params->isVisible = (bool) $handle->show_on_profile;
            if (!is_null($handle->url)) {
                $params->url = $handle->url;
            }
        }
        return $params;
    }

    public static function add($params)
    {
        $handle = new self();
        foreach ($params as $key=>$value) {
            $handle->$key = $value;
        }
        $handle->create($params);
    }

    public static function modify($params)
    {
        $handle = new self();
        foreach ($params as $key=>$value) {
            $handle->$key = $value;
        }
        $handle->update($params);
    }

    public static function delete($id)
    {
    }
}
