<?php

namespace Courseware;

/**
 * Courseware's bookmarks.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property array                         $id         computed column read/write
 * @property string                        $user_id    database column
 * @property int                           $element_id database column
 * @property int                           $mkdate     database column
 * @property int                           $chdate     database column
 * @property \User                         $user       belongs_to User
 * @property \Courseware\StructuralElement $element    belongs_to Courseware\StructuralElement
 */
class Bookmark extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_bookmarks';

        $config['belongs_to']['user'] = [
            'class_name' => \User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'element_id',
        ];

        parent::configure($config);
    }

    /**
     * Returns the range object this bookmark belongs to.
     *
     * @return \Range the range object of this object
     */
    public function getRange(): \Range
    {
        $rangeType = $this->element['range_type'];

        return $this->element->$rangeType;
    }

    /**
     * Returns all bookmarks of a user.
     *
     * @param string $userId the user's ID for whom to search for bookmarks
     *
     * @return Bookmark[] the list of bookmarks
     */
    public function findUsersBookmarks(string $userId): array
    {
        return self::findBySQL('user_id = ?', [$userId]);
    }
}
