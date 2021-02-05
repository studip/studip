<?php


/**
 * Clipboard.class.php - model class for a clipboard (Merkzettel)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * The Clipboard class extends the wish list functionality of the clipboard
 * in the old literature management to allow it to be used in other areas of the
 * Stud.IP system. Furthermore, clipboards managed by this class
 * are stored permanently in the database until they are deleted.
 *
 * @property string id database column
 * @property string user_id database column
 * @property string name database column: The name of the clipboard.
 * @property string allowed_item_class database column: The StudipItem class
 *     name where items have to be children of in order to be used
 *     with the clipboard.
 *     This attribute defaults to 'StudipItem' which means that by default
 *     all implementations of StudipItem can be inserted into a clipboard.
 *     If only items of a special implementation of StudipItem shall be
 *     able to be inserted into the clipboard this attribute has to be set
 *     to the class name of that implementation.
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection user belongs_to User
 * @property SimpleORMapCollection items has_many WishListItem
 */
class Clipboard extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'clipboards';

        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_func' => 'find'
        ];

        $config['has_many']['items'] = [
            'class_name' => 'ClipboardItem',
            'assoc_foreign_key' => 'clipboard_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        parent::configure($config);
    }


    /**
     * This class returns all clipboards for a specified user,
     * optionally filtered by required item range types.
     *
     * @param string $user_id: The ID of the user whose clipboards
     *     shall be returned.
     * @param array $item_range_types: The range types which at least one item
     *     of the clipboard must have to be included in the result set.
     *
     * @returns Clipboard[] An array of clipboard objects which are
     *     associated with the user specified by $user_id.
     */
    public static function getClipboardsForUser($user_id = '', $item_range_types = [])
    {
        if (!$user_id) {
            return [];
        }

        if (is_array($item_range_types) and count($item_range_types) > 0) {
            return self::findBySql(
                "INNER JOIN clipboard_items
                ON clipboards.id = clipboard_items.clipboard_id
                WHERE
                clipboards.user_id = :user_id
                AND
                clipboard_items.range_type IN ( :range_types )
                GROUP BY clipboards.id ORDER BY name ASC, mkdate ASC",
                [
                    'user_id' => $user_id,
                    'range_types' => $item_range_types
                ]
            );
        } else {
            return self::findBySql(
                'user_id = :user_id ORDER BY name ASC, mkdate ASC',
                [
                    'user_id' => $user_id
                ]
            );
        }
    }


    /**
     * Adds an item to the clipboard by specifying the
     * ID of the object that shall be added to it.
     *
     * @param string $range_id The ID of the object that shall be added.
     *
     * @throws ClipboardException If an error occurs.
     *
     * @returns ClipboardItem A ClipboardItem instance on success,
     *     false on failure.
     */
    public function addItem($range_id = null, $range_type = 'StudipItem')
    {
        if (($range_id === null) or !is_a($range_type, 'StudipItem', true)) {
            //Either the range_id is not set or the range_type does not
            //name a StudipItem class.
            throw new ClipboardException(
                _('Die Daten zum Hinzufügen eines Eintrags sind ungültig!')
            );
        }

        //Check if the item already exists:

        $item = ClipboardItem::findOneBySql(
            'clipboard_id = :clipboard_id
            AND range_id = :range_id
            AND range_type = :range_type',
            [
                'clipboard_id' => $this->id,
                'range_id' => $range_id,
                'range_type' => $range_type
            ]
        );
        if ($item) {
            //Item already exists and nothing has to be modified.
            return $item;
        }

        //Item does not exist: create it.
        $item = new ClipboardItem();
        $item->clipboard_id = $this->id;
        $item->range_id = $range_id;
        $item->range_type = $range_type;
        if (!$item->store()) {
            throw new ClipboardException(
                _('Fehler beim Speichern des Eintrags!')
            );
        }
        return $item;
    }


    /**
     * Removes an item from the clipboard by specifying the ID of the
     * object that shall be removed from the pad.
     *
     * @param string $range_id The ID of the object that shall be removed.
     *
     * @returns bool True on success, false on failure.
     */
    public function removeItem($range_id = null)
    {
        if ($range_id === null) {
            //The range_id is not set.
            return false;
        }

        return ClipboardItem::deleteBySql(
            'clipboard_id = :clipboard_id AND range_id = :range_id',
            [
                'clipboard_id' => $this->id,
                'range_id' => $range_id
            ]
        ) > 0;
    }


    /**
     * Formats the content of the clipboard for display.
     * This method should be overloaded by derived classes
     * to output the data of the clipboard with appropriate names
     * and other attributes which may be needed.
     *
     * @returns string[][] A two-dimensional array with strings.
     *     The first dimension represents the list of clipboard items.
     *     The second dimension represents an item and holds at least
     *     the following attributes of the item: id, name.
     *     These attributes are the keys of the second array dimension.
     *     The array has the following structure:
     *     [
     *         [
     *             'id' => (id of the item)
     *             'name' => (name of the item)
     *         ],
     *         [
     *             …
     *         ]
     *     ]
     *
     *     Derived classes may add further attributes to the array,
     *     if necessary.
     */
    public function getContent()
    {
        if (!$this->items) {
            return [];
        }

        $content = [];
        foreach ($this->items as $item) {
            //Only those elements which store the IDs of objects
            //from the allowed content class or its descendants
            //are added to the $content array.
            if (is_a($item->range_type, $this->allowed_item_class, true)) {
                $content[] = [
                    'id' => $item->id,
                    'range_type' => $item->range_type,
                    'range_id' => $item->range_id,
                    'name' => $item->__toString()
                ];
            }
        }

        return $content;
    }


    /**
     * Retrieves all range-IDs of objects that are associated
     * with this clipboard. The objects can be filtered by their
     * range type.
     *
     * @param string|string[] $range_types The class name(s) of the objects
     *     which shall be included in the result set.
     *     This parameter can be a string or an array of strings.
     *
     * @returns string[] An array with all range-IDs that match
     *     the specified range type.
     */
    public function getAllRangeIds($range_types = 'StudipItem')
    {
        if (!$range_types) {
            //If no range types are specified we cannot retrieve range-IDs:
            return [];
        }

        if (!is_array($range_types)) {
            //Make $range_types an array:
            $range_types = [$range_types];
        }

        $db = DBManager::get();

        $stmt = $db->prepare(
            "SELECT range_id FROM clipboard_items
             WHERE range_type IN ( :range_types )
             AND clipboard_id = :clipboard_id;"
        );

        $stmt->execute(
            [
                'range_types' => $range_types,
                'clipboard_id' => $this->id
            ]
        );

        return $stmt->fetchAll(
            PDO::FETCH_COLUMN,
            0
        );
    }


    /**
     * Retrieves specific range-IDs of objects that are associated
     * with this clipboard and referenced by the specified clipboard item-IDs.
     * The objects can be filtered by their range type, too.
     *
     * @param string|string[] $range_types The class name(s) of the objects
     *     which shall be included in the result set. This parameter
     *     can be an array or a string.
     *
     * @param string[] $item_ids The item-IDs of the clipboard items
     *     whose range-IDs shall be included in the result set.
     *     Note that the clipboard items must be associated with this clipboard.
     *
     * @returns string[] An array with all range-IDs that match
     *     the specified range type.
     */
    public function getSomeRangeIds($range_types = 'StudipItem', $item_ids = [])
    {
        if ((!is_array($item_ids) and !$item_ids) or !$range_types) {
            //Item-IDs is either an array or empty or it isn't even an array
            //or $range_types is not set.
            //We cannot use it to retrieve range-IDs.
            return [];
        }

        $db = DBManager::get();

        if (!is_array($range_types)) {
            //Make $range_types an array:
            $range_types = [$range_types];
        }

        $stmt = $db->prepare(
            "SELECT range_id FROM clipboard_items
            WHERE range_type IN ( :range_types )
            AND clipboard_id = :clipboard_id
            AND id IN ( :item_ids );"
        );

        $stmt->execute(
            [
                'range_types' => $range_types,
                'clipboard_id' => $this->id,
                'item_ids' => $item_ids
            ]
        );

        return $stmt->fetchAll(
            PDO::FETCH_COLUMN,
            0
        );
    }
}
