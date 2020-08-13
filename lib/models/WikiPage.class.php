<?php
/**
 * WikiPage.class.php
 * model class for table wiki
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    mlunzena
 * @copyright (c) Authors
 *
 * @property string range_id database column
 * @property string user_id database column
 * @property string keyword database column
 * @property string body database column
 * @property string chdate database column
 * @property string version database column
 * @property string id computed column read/write
 * @property User author belongs_to User
 */
class WikiPage extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures the model
     * @param  array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'wiki';

        $config['belongs_to']['author'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'range_id',
        ];

        $config['additional_fields']['config']['get'] = function ($page) {
            return new WikiPageConfig([$page->range_id, $page->keyword]);
        };

        $config['registered_callbacks']['before_delete'][] = function ($page) {
            if ($page->version == 1 && $page->config) {
                $page->config->delete();
            }
        };

        $config['default_values']['user_id'] = 'nobody';

        parent::configure($config);
    }

    /**
     * Finds all latest versions of all pages for the given course.
     * @param  string $course_id Course id
     * @return SimpleCollection of all pages
     */
    public static function findLatestPages($course_id)
    {
        $query = "SELECT
                    range_id,
                    keyword,
                    MAX(version) as version
                  FROM wiki
                  WHERE range_id = ?
                  GROUP BY keyword
                  ORDER BY keyword ASC";

        $st = DBManager::get()->prepare($query);
        $st->execute([$course_id]);
        $ids = $st->fetchAll(PDO::FETCH_NUM);

        $pages = new SimpleORMapCollection();
        $pages->setClassName(__CLASS__);

        foreach ($ids as $id) {
            $pages[] = self::find($id);

        }

        return $pages;
    }

    /**
     * Finds the latest version for the given course and keyword
     * @param  string $course_id Course id
     * @param  string $keyword   Keyword
     * @return WikiPage or null
     */
    public static function findLatestPage($course_id, $keyword)
    {
        $results = self::findBySQL(
            "range_id = ? AND keyword = ? ORDER BY version DESC LIMIT 1",
            [$course_id, $keyword]
        );

        if (count($results) === 0) {
            return null;
        }

        return $results[0];
    }

    /**
     * Returns whether this page is visible to the given user.
     * @param  mixed  $user User object or id
     * @return boolean indicating whether the page is visible
     */
    public function isVisibleTo($user)
    {
        // anyone can see this page if it belongs to a free course
        if (!$this->config->read_restricted
            && Config::get()->ENABLE_FREE_ACCESS
            && $this->course && $this->course->lesezugriff == 0)
        {
            return true;
        }

        return $GLOBALS['perm']->have_studip_perm(
            $this->config->read_restricted ? 'tutor' : 'user',
            $this->range_id,
            is_object($user) ? $user->id : $user
        );
    }

    /**
     * Returns whether this page is editable to the given user.
     * @param  mixed  $user User object or id
     * @return boolean indicating whether the page is editable
     */
    public function isEditableBy($user)
    {
        return $GLOBALS['perm']->have_studip_perm(
            $this->config->edit_restricted ? 'tutor' : 'autor',
            $this->range_id,
            is_object($user) ? $user->id : $user
        );
    }

    /**
     * Returns whether this page is creatable to the given user.
     * @param  mixed  $user User object or id
     * @return boolean indicating whether the page is creatable
     * @todo this method is kinda bogus as an instance method
     */
    public function isCreatableBy($user)
    {
        return $this->isEditableBy($user);
    }

    /**
     * Returns whether this version of this page is the latest version availabe.
     * @return boolean
     */
    public function isLatestVersion()
    {
        return self::countBySQL(
            'range_id = ? AND keyword = ? AND version > ?',
            [$this->range_id, $this->keyword, $this->version]
        ) === 0;
    }

    /**
     * Returns the start page of a wiki for a given course. The start page has
     * the keyword 'WikiWikiWeb'.
     *
     * @param  string $course_id Course id
     * @return WikiPage
     */
    public static function getStartPage($course_id)
    {
        $start = self::findLatestPage($course_id, '');

        if (!$start) {
            $start = new self([$course_id, 'WikiWikiWeb', 0]);
            $start->body = _('Dieses Wiki ist noch leer.');

            if ($start->isEditableBy($GLOBALS['user'])) {
                $start->body .=  ' ' . _("Bearbeiten Sie es!\nNeue Seiten oder Links werden einfach durch Eingeben von [nop][[Wikinamen]][/nop] in doppelten eckigen Klammern angelegt.");
            }
        }

        return $start;
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
                $storage->addTabularData(_('Wiki Einträge'), 'wiki', $field_data);
            }
        }
    }
}
