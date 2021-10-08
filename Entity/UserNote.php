<?php

namespace ThemeHouse\UserNotes\Entity;

use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class UserNote
 * @package ThemeHouse\UserNotes\Entity
 *
 * COLUMNS
 * @property integer user_note_id
 * @property string message
 * @property integer post_date
 * @property integer last_edit_date
 * @property integer profile_user_id
 * @property integer user_id
 *
 * RELATIONS
 * @property User ProfileUser
 * @property User User
 */
class UserNote extends Entity
{
    /**
     * @return bool
     */
    public function canView()
    {
        return \XF::visitor()->hasPermission('th_usernotes', 'view');
    }

    /**
     * @return bool
     */
    public function canEdit()
    {
        $visitor = \XF::visitor();

        return $visitor->hasPermission('th_usernotes', 'edit_all')
            || ($visitor->user_id == $this->user_id && $visitor->hasPermission('th_usernotes', 'edit'));
    }

    /**
     * @return bool
     */
    public function canDelete()
    {
        $visitor = \XF::visitor();

        return $visitor->hasPermission('th_usernotes', 'delete_all')
            || ($visitor->user_id == $this->user_id && $visitor->hasPermission('th_usernotes', 'delete'));
    }

    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_usernotes_usernote';
        $structure->shortName = 'ThemeHouse\UserNotes:UserNote';
        $structure->primaryKey = 'user_note_id';
        $structure->columns = [
            'user_note_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'message' => [
                'type' => self::STR,
                'required' => 'please_enter_valid_message',
                'api' => true
            ],
            'post_date' => ['type' => self::UINT, 'required' => true, 'default' => \XF::$time, 'api' => true],
            'last_edit_date' => [
                'type' => self::UINT,
                'nullable' => true,
                'required' => true,
                'default' => null,
                'api' => true
            ],
            'profile_user_id' => ['type' => self::UINT, 'required' => true, 'api' => true],
            'user_id' => ['type' => self::UINT, 'required' => true, 'api' => true],
            'username' => [
                'type' => self::STR,
                'maxLength' => 50,
                'required' => 'please_enter_valid_name'
            ],
        ];

        $structure->getters = [];

        $structure->relations = [
            'ProfileUser' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => [['user_id', '=', '$profile_user_id']],
                'primary' => true
            ],
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true,
                'api' => true
            ],
        ];

        return $structure;
    }
}