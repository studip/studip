<?php
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2005 Tobias Thelen ,   <tthelen@uni-osnabrueck.de>
//
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

/**
 * StudipComment.class.php
 *
 *
 *
 *
 * @author   André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
 * @access   public
 *
 * @property string comment_id database column
 * @property string id alias column for comment_id
 * @property string object_id database column
 * @property string user_id database column
 * @property string content database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property StudipNews news belongs_to StudipNews
 */

class StudipComment extends SimpleORMap implements PrivacyObject
{

    static function NumCommentsForObject($object_id)
    {
        return self::countBySql('object_id = ?', [$object_id]);
    }

    static function NumCommentsForObjectSinceLastVisit($object_id, $comments_since = 0, $exclude_user_id = null)
    {
        $query = "object_id = ?";
        $query .= " AND chdate > ?";
        if ($exclude_user_id) $query .= " AND user_id != ?";
        return self::countBySql($query, func_get_args());
    }

    static function GetCommentsForObject($object_id)
    {
        global $_fullname_sql;
        $query = "SELECT comments.content, {$_fullname_sql['full']} AS fullname,
                         a.username, comments.mkdate, comments.comment_id
                  FROM comments
                  LEFT JOIN auth_user_md5 AS a USING (user_id)
                  LEFT JOIN user_info USING (user_id)
                  WHERE object_id = ?
                  ORDER BY comments.mkdate";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$object_id]);
        return $statement->fetchAll(PDO::FETCH_BOTH);
    }

    static function DeleteCommentsByObject($object_ids)
    {
        if (!is_array($object_ids)) {
            $object_ids = [$object_ids];
        }
        $where = "object_id IN (?)";
        return self::deleteBySQL($where, [$object_ids]);
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'comments';
        $config['belongs_to']['news'] = [
            'class_name' => 'StudipNews',
            'foreign_key' => 'object_id',
        ];
        parent::configure($config);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Ankündigungen Kommentare'), 'comments', $field_data);
            }
        }
    }
}
