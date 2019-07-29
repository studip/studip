<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2005 André Noack <noack@data-quest>,
// Suchi & Berg GmbH <info@data-quest.de>
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
require_once 'lib/object.inc.php';

/**
 * StudipNews.class.php
 *
 * @author   André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
 * @author   Arne Schröder <schroeder@data-quest>
 * @access   public
 *
 * @property string news_id database column
 * @property string id alias column for news_id
 * @property string topic database column
 * @property string body database column
 * @property string author database column
 * @property string date database column
 * @property string user_id database column
 * @property string expire database column
 * @property string allow_comments database column
 * @property string chdate database column
 * @property string chdate_uid database column
 * @property string mkdate database column
 * @property SimpleORMapCollection news_ranges has_many NewsRange
 * @property SimpleORMapCollection comments has_many StudipComment
 * @property User owner belongs_to User
 */
class StudipNews extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'news';
        $config['has_many']['news_ranges'] = [
            'class_name'        => 'NewsRange',
            'assoc_foreign_key' => 'news_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];
        $config['has_many']['comments'] = [
            'class_name'        => 'StudipComment',
            'assoc_foreign_key' => 'object_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];
        $config['belongs_to']['owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }

    public static function GetNewsByRange($range_id, $only_visible = false, $as_objects = false)
    {
        if ($only_visible){
            $clause = " AND date < UNIX_TIMESTAMP() AND (date+expire) > UNIX_TIMESTAMP() ";
        }
        $query = "SELECT news_id AS idx, news.*
                  FROM news_range
                  INNER JOIN news USING (news_id)
                  WHERE range_id = ? {$clause} ";
        if (Config::get()->SORT_NEWS_BY_CHDATE) {
            $query .= "ORDER BY chdate DESC, date DESC, topic ASC";
        } else {
            $query .= "ORDER BY date DESC, chdate DESC, topic ASC";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        $ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        return ($as_objects ? StudipNews::GetNewsObjects($ret) : $ret);
    }

    public static function CountUnread($range_id = 'studip', $user_id = false)
    {
        $query = "SELECT SUM(chdate > IFNULL(b.visitdate, :threshold) AND nw.user_id != :user_id)
                  FROM news_range a
                  LEFT JOIN news nw ON (a.news_id = nw.news_id AND UNIX_TIMESTAMP() BETWEEN date AND date + expire)
                  LEFT JOIN object_user_visits b ON (b.object_id = nw.news_id AND b.user_id = :user_id AND b.type = 'news')
                  WHERE a.range_id = :range_id
                  GROUP BY a.range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->bindValue(':user_id', $user_id ?: $GLOBALS['user']->id);
        $statement->bindValue(':range_id', $range_id);
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    public static function GetNewsByAuthor($user_id, $as_objects = false)
    {
        $query = "SELECT news_id AS idx, news.*
                  FROM news
                  WHERE user_id = ? ";
        if (Config::get()->SORT_NEWS_BY_CHDATE) {
            $query .= "ORDER BY chdate DESC, date DESC";
        } else {
            $query .= "ORDER BY date DESC, chdate DESC";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        return $as_objects ? StudipNews::GetNewsObjects($ret) : $ret;
    }

    public static function GetNewsByRSSId($rss_id, $as_objects = false)
    {
        if ($user_id = StudipNews::GetUserIDFromRssID($rss_id)){
            return StudipNews::GetNewsByRange($user_id, true, $as_objects);
        }

        return [];
    }

    public static function GetNewsObjects($news_result)
    {
        $objects = [];
        if (is_array($news_result)){
            foreach ($news_result as $id => $result){
                $objects[$id] = new StudipNews();
                $objects[$id]->setData($result, true);
                $objects[$id]->setNew(false);
            }
        }
        return $objects;
    }

    /**
     * fetches set of news items from database
     *
     * @param string $user_id         author id for news set
     * @param string $area            area group for news set (global, inst, sem or user)
     * @param string $term            search term for news topic
     * @param int $startdate          return only news (still) visible after this date
     * @param int $enddate            return only news (still) visible before this date
     * @param boolean $as_objects     include StudipNews objects in result array
     * @param int $limit              max size of returned news set
     * @return array                  set of news items
     */
    public static function GetNewsRangesByFilter($user_id, $area = '', $term = '', $startdate = 0, $enddate = 0, $as_objects = false, $limit = 100)
    {
        $news_result    = [];
        $query_vars     = [];

        if ($limit <= 0) {
            return $news_result;
        }

        if (isset($startdate)) {
            $where_querypart[]  = "(date+expire) > ?";
            $query_vars[]       = $startdate;
        }
        if (isset($enddate)) {
            $where_querypart[]  = "date < ?";
            $query_vars[]       = $enddate;
        }

        if(!$GLOBALS['perm']->have_perm('root') || $area !== 'global') {
            $where_querypart[]  = 'news.user_id = ?';
            $query_vars[]       = $user_id;
        }

        if (isset($term)) {
            $where_querypart[]  = "topic LIKE CONCAT('%', ?, '%')";
            $query_vars[]       = $term;
        }
        switch ($area) {
            case 'global':
                $select_querypart   = 'CONCAT(news_id, "_studip") AS idx, range_id, news.* ';
                $from_querypart     = 'news_range INNER JOIN news USING(news_id)';
                $where_querypart[]  = 'range_id = ?';
                if (Config::get()->SORT_NEWS_BY_CHDATE) {
                    $order_querypart = 'news.chdate DESC, news.date DESC';
                } else {
                    $order_querypart = 'news.date DESC, news.chdate DESC';
                }
                $query_vars[]       = 'studip';
                break;
            case 'sem':
                $select_querypart   = 'CONCAT(news_id, "_", range_id) AS idx, range_id, seminare.Name AS title, '
                    .'seminare.start_time AS start, news.*, seminare.start_time, sd1.name AS startsem, '
                    .'IF(seminare.duration_time=-1, "'._("unbegrenzt").'", sd2.name) AS endsem ';
                $from_querypart     = 'news INNER JOIN news_range USING(news_id) INNER JOIN seminare ON Seminar_id = range_id '
                    .'LEFT JOIN semester_data sd1 ON (start_time BETWEEN sd1.beginn AND sd1.ende) '
                    .'LEFT JOIN semester_data sd2 ON (start_time + duration_time BETWEEN sd2.beginn AND sd2.ende)';
                if (Config::get()->SORT_NEWS_BY_CHDATE) {
                    $order_querypart = 'seminare.Name, news.chdate DESC, news.date DESC';
                } else {
                    $order_querypart = 'seminare.Name, news.date DESC, news.chdate DESC';
                }
                break;
            case 'inst':
                $select_querypart   = 'CONCAT(news_id, "_", range_id) AS idx, range_id, Institute.Name AS title, news.* ';
                $from_querypart     = 'Institute INNER JOIN news_range ON Institut_id = range_id INNER JOIN news USING(news_id)';
                if (Config::get()->SORT_NEWS_BY_CHDATE) {
                    $order_querypart = 'Institute.Name, news.chdate DESC, news.date DESC';
                } else {
                    $order_querypart = 'Institute.Name, news.date DESC, news.chdate DESC';
                }
                break;
            case 'user':
                $select_querypart   = 'CONCAT(news_id, "_", auth_user_md5.user_id) AS idx, range_id, auth_user_md5.user_id AS userid, news.* ';
                $from_querypart     = 'auth_user_md5 INNER JOIN news_range ON auth_user_md5.user_id = range_id INNER JOIN news USING(news_id)';
                if (Config::get()->SORT_NEWS_BY_CHDATE) {
                    $order_querypart = 'auth_user_md5.Nachname, news.chdate DESC, news.date DESC';
                } else {
                    $order_querypart = 'auth_user_md5.Nachname, news.date DESC, news.chdate DESC';
                }
                break;
            default:
                foreach (['global', 'inst', 'sem', 'user'] as $type) {
                    $add_news = StudipNews::GetNewsRangesByFilter($user_id, $type, $term, $startdate, $enddate, $as_objects, $limit);
                    if (is_array($add_news) && isset($add_news[$type])) {
                        $limit          = $limit - count($add_news[$type]);
                        $news_result    = array_merge($news_result, $add_news);
                    }
                }
                return $news_result;
        }
        $query = "SELECT " . $select_querypart . "
                  FROM " . $from_querypart . "
                  WHERE " . implode(' AND ', $where_querypart) . "
                  ORDER BY " . $order_querypart . " LIMIT 0, ?";

        $query_vars[]   = $limit;
        $statement      = DBManager::get()->prepare($query);
        $statement->execute($query_vars);
        $news_result    = $statement->fetchGrouped(PDO::FETCH_ASSOC);
        if (is_array($news_result)) {
            foreach($news_result as $id => $result) {
                $objects[$area][$id]['range_id']    = $result['range_id'];
                $objects[$area][$id]['title']       = $result['title'];
                if ($area == 'sem') {
                    $objects[$area][$id]['semester'] .= sprintf('(%s%s)',
                        $result['startsem'],
                        $result['startsem'] != $result['endsem'] ? ' - ' . $result['endsem'] : '');
                } elseif ($area == 'user') {
                    if ($GLOBALS['user']->id == $result['userid']) {
                        $objects[$area][$id]['title'] = _('Ankündigungen auf Ihrer Profilseite');
                    }
                    else {
                        $objects[$area][$id]['title'] = sprintf(_('Ankündigungen auf der Profilseite von %s'), get_fullname($result['userid']));
                    }
                } elseif ($area == 'global') {
                    $objects[$area][$id]['title'] = _('Ankündigungen auf der Stud.IP Startseite');
                }
                if ($as_objects) {
                    $objects[$area][$id]['object'] = new StudipNews();
                    $objects[$area][$id]['object']->setData($result, true);
                    $objects[$area][$id]['object']->setNew(false);
                }
            }
        }
        return $objects;
    }

    public static function GetUserIdFromRssID($rss_id)
    {
        $ret = StudipNews::GetRangeIdFromRssID($rss_id);
        return $ret['range_id'];
    }

    public static function GetRssIdFromUserId($user_id)
    {
        return StudipNews::GetRssIdFromRangeId($user_id);
    }

    public static function GetRangeFromRssID($rss_id)
    {
        if ($rss_id){
            $query = "SELECT range_id ,range_type
                      FROM news_rss_range
                      WHERE rss_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$rss_id]);
            $ret = $statement->fetch(PDO::FETCH_ASSOC);

            if (count($ret)) {
                return $ret;
            }
        }
        return false;
    }

    public static function GetRangeIdFromRssID($rss_id)
    {
        $ret = StudipNews::GetRangeFromRssID($rss_id);
        return $ret['range_id'];
    }

    public static function GetRssIdFromRangeId($range_id)
    {
        $query = "SELECT rss_id FROM news_rss_range WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        return $statement->fetchColumn();
    }

    public static function SetRssId($range_id, $type = false)
    {
        if (!$type){
            $type = get_object_type($range_id);
            if ($type === 'fak') {
                $type = 'inst';
            }
        }
        $rss_id = md5('StudipRss' . $range_id);

        $query = "REPLACE INTO news_rss_range (range_id,rss_id,range_type)
                  VALUES (?, ?, ?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $range_id,
            $rss_id,
            $type
        ]);
        return $statement->rowCount();
    }

    public static function UnsetRssId($range_id)
    {
        $query = "DELETE FROM news_rss_range WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        return $statement->rowCount();
    }

    public static function GetAdminMsg($user_id, $date)
    {
        return sprintf(
            _('Zuletzt aktualisiert von %s (%s) am %s'),
            get_fullname($user_id),
            get_username($user_id),
            date('d.m.y', $date)
        );
    }

    public static function DoGarbageCollect()
    {
        $db = DBManager::get();
        if (!Config::get()->NEWS_DISABLE_GARBAGE_COLLECT) {
            $query = "SELECT news.news_id
                      FROM news
                      WHERE date + expire < UNIX_TIMESTAMP()

                      UNION DISTINCT

                      SELECT news_range.news_id
                      FROM news_range
                      LEFT JOIN news USING (news_id)
                      WHERE news.news_id IS NULL

                      UNION DISTINCT

                      SELECT news.news_id
                      FROM news
                      LEFT JOIN news_range USING (news_id)
                      WHERE range_id IS NULL";
            $result = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);

            if (count($result) > 0) {
                $query = "DELETE FROM news WHERE news_id IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$result]);
                $killed = $statement->rowCount();

                $query = "DELETE FROM news_range WHERE news_id IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$result]);

                object_kill_visits(null, $result);
                object_kill_views($result);
                StudipComment::DeleteCommentsByObject($result);
            }
            return $killed;
        }
    }

    /**
     * DEPRECATED
     */
    public static function TouchNews($news_id, $touch_stamp = null)
    {
        $ret = false;
        if (!$touch_stamp) {
            $touch_stamp = time();
        }
        $news = new StudipNews($news_id);
        if (!$news->isNew()) {
            $news->date = strtotime('today 0:00:00', $touch_stamp);
            if (!$news->store()) {
                $news->triggerChdate();
            }
        }
        return $ret;
    }

    public static function DeleteNewsRanges($range_id)
    {
        $query = "DELETE FROM news_range WHERE range_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $range_id);
        $result = $statement->execute();

        StudipNews::DoGarbageCollect();

        return $result;
    }

    public static function DeleteNewsByAuthor($user_id)
    {
        foreach (StudipNews::GetNewsByAuthor($user_id, true) as $news) {
            $deleted += $news->delete();
        }
        return $deleted;
    }

    public static function haveRangePermission($operation, $range_id, $user_id = '')
    {
        static $news_range_perm_cache;
        if (isset($news_range_perm_cache[$user_id.$range_id.$operation])) {
            return $news_range_perm_cache[$user_id.$range_id.$operation];
        }
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        if ($GLOBALS['perm']->have_perm('root', $user_id)) {
            return $news_range_perm_cache[$user_id.$range_id.$operation] = true;
        }

        $type = get_object_type($range_id, ['global', 'sem', 'inst', 'fak', 'user']);
        switch($type) {
            case 'global':
                if ($operation === 'view')
                    return $news_range_perm_cache[$user_id.$range_id.$operation] = true;
                break;
            case 'fak':
            case 'inst':
            case 'sem':
                if ($operation === 'view'
                    && ($type !== 'sem'
                        || $GLOBALS['perm']->have_studip_perm('user', $range_id)
                        || (Config::get()->ENABLE_FREE_ACCESS && Seminar::getInstance($range_id)->read_level == 0)
                        )) {
                        return $news_range_perm_cache[$user_id.$range_id.$operation] = true;
                    }
                if ($operation === 'edit' || $operation === 'copy') {
                    if ($GLOBALS['perm']->have_studip_perm('tutor', $range_id))
                        return $news_range_perm_cache[$user_id.$range_id.$operation] = true;
                }
                break;
            case 'user':
                if ($operation === 'view') {
                    if ($range_id === $user_id || get_visibility_by_id($range_id)) {
                        return $news_range_perm_cache[$user_id.$range_id.$operation] = true;
                    }
                } elseif ($operation === 'edit' || $operation == 'copy') {
                    if ($GLOBALS['perm']->have_profile_perm('user', $range_id))
                        return $news_range_perm_cache[$user_id.$range_id.$operation] = true;
                }
                break;
        }
        return $news_range_perm_cache[$user_id.$range_id.$operation] = false;
    }

    public function restoreRanges()
    {
        $this->resetRelation('news_ranges');
        return count($this->news_ranges);
    }

    public function getRanges()
    {
        $ranges = $this->news_ranges->pluck('range_id');
        return $ranges;
    }

    public function issetRange($range_id)
    {
        return array_search($range_id, $this->getRanges()) !== false;
    }

    public function addRange($range_id)
    {
        if (!$this->issetRange($range_id)) {
            $range = new NewsRange([$this->getId(), $range_id]);
            if ($range->isNew()) {
                $range->range_id = $range_id;
                $range->news_id = $this->getId();
            }
            $this->news_ranges[] = $range;
            return true;
        }

        return false;
    }

    public function deleteRange($range_id)
    {
        if ($this->issetRange($range_id)) {
            return $this->news_ranges->unsetBy('range_id', $range_id);
        }

        return false;
    }

    public function storeRanges()
    {
        $this->storeRelations();
    }

    public function delete()
    {
        object_kill_visits(null, $this->getId());
        object_kill_views($this->getId());
        return parent::delete();
    }

    /**
     * checks, if user has permission to perform given operation on news object
     *
     * @param string $operation       delete, unassign, edit, copy, or view
     * @param string $check_range_id  specified range-id, used only for unassign-operation
     * @param string $user_id         optional; check permission for
     *                                given user ID; otherwise for the
     *                                global $user's ID
     * @return boolean true or false
     */
    public function havePermission($operation, $check_range_id = '', $user_id = null)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        if (!in_array($operation, ['delete', 'unassign', 'edit', 'copy', 'view'])) {
            return false;
        }

        // in order to unassign, there must be more than one range assigned; $check_range_id must be specified.
        if ($operation === 'unassign' && count($this->getRanges()) < 2) {
            return false;
        }
        // root, owner, and owner's deputy have full permission
        if ($GLOBALS['perm']->have_perm('root', $user_id)
            || ($user_id === $this->user_id && $GLOBALS['perm']->have_perm('autor'))
            || (isDeputyEditAboutActivated() && isDeputy($user_id, $this->user_id, true)))
        {
            return true;
        }

        // check news' ranges for edit, copy or view permission
        if ($operation === 'unassign' || $operation === 'delete') {
            $range_operation = 'edit';
        } else {
            $range_operation = $operation;
        }
        foreach ($this->getRanges() as $range_id) {
            if (StudipNews::haveRangePermission($range_operation, $range_id, $user_id)) {
                if ($operation === 'view' || $operation === 'edit' || $operation === 'copy') {
                    // in order to view, edit, copy, or unassign, access to one of the ranges is sufficient
                    return true;
                } elseif ($operation === 'unassign' && $range_id === $check_range_id) {
                    // in order to unassign, access to the specified range is needed
                    return true;
                }
                // in order to delete, access to all ranges is necessary
                $permission_ranges += 1;
            } elseif ($operation === 'delete') {
                return false;
            }
        }
        if ($operation === 'delete' && count($this->getRanges()) == $permission_ranges) {
            return true;
        }

        return false;
    }

    /**
     * checks, if basic news data is complete
     *
     * @return boolean true or false
     */
    public function validate()
    {
        if (!$this->user_id && $this->isNew()) {
            $this->user_id = $GLOBALS['user']->id;
            $this->author = get_fullname(false, 'full', false);
        }
        if (!$this->user_id OR !$this->author) {
            PageLayout::postError(_('Fehler: Personenangabe unvollständig.'));
            return false;
        }
        if (!$this->topic) {
            PageLayout::postError(_('Bitte geben Sie einen Titel für die Ankündigung ein.'));
            return false;
        }
        if (!$this->body) {
            PageLayout::postError(_('Bitte geben Sie einen Inhalt für die Ankündigung ein.'));
            return false;
        }
        if (!count($this->getRanges())) {
            PageLayout::postError(_('Die Ankündigung muss mindestens einem Bereich zugeordnet sein.'));
            return false;
        }
        if ((int)$this->date < 1) {
            PageLayout::postError(_('Ungültiges Einstelldatum.'));
            return false;
        }
        if ((int)$this->expire < 1) {
            PageLayout::postError(_('Ungültiges Ablaufdatum.'));
            return false;
        }

        return true;
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
                $storage->addTabularData(_('Ankündigungen'), 'news', $field_data);
            }
        }
    }
}
