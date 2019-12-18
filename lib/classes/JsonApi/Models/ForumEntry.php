<?php

namespace JsonApi\Models;

/*
 * @property string category_id string primary key
 * @property string seminar_id string foreign key
 * @property string entry_name string database_column
 * @property string pos int
 */

class ForumEntry extends \SimpleORMap
{
    public static function getCatFromEntry($topicId)
    {
        $targetEntry = ForumEntry::find($topicId);
        $parentEntries = ForumEntry::findBySQL(
            'forum_entries.lft <= ? AND forum_entries.rgt >= ? AND forum_entries.seminar_id = ? AND forum_entries.depth = 1 ORDER BY forum_entries.lft ASC',
            [(int) $targetEntry['lft'], (int) $targetEntry['rgt'], $targetEntry['seminar_id']]
        );
        $category = ForumCat::findBySQL(
            'LEFT JOIN forum_categories_entries AS fce USING (category_id)
            WHERE fce.topic_id = ?',
            [$parentEntries[0]->id]
        );

        if (empty($category)) {
            $category = ForumCat::findBySql(
                "seminar_id = ? AND entry_name = 'Allgemein'",
                [$targetEntry->seminar_id]
            );
        }

        return $category;
    }

    public static function getCategories($seminarId, $asObjects = false)
    {
        $stmt = \DBManager::get()->prepare('SELECT * FROM forum_categories
            WHERE seminar_id = ? ORDER BY pos ASC');
        $stmt->execute([$seminarId]);
        $ret = $stmt->fetchGrouped(\PDO::FETCH_ASSOC);

        return $asObjects ? ForumCat::getCatObjects($ret) : $ret;
    }

    public static function getEntriesFromCat(ForumCat $targetCategory)
    {
        return ForumEntry::findBySQL(
            'LEFT JOIN forum_categories_entries '
            .'ON forum_categories_entries.topic_id = forum_entries.topic_id '
            .'WHERE forum_categories_entries.category_id = ? '
            .'ORDER BY forum_entries.lft DESC',
            [$targetCategory->id]
        );
    }

    public static function getChildEntries($topicId)
    {
        $targetEntry = ForumEntry::find($topicId);

        return ForumEntry::findBySQL(
            'lft > ? AND rgt < ? AND seminar_id = ? ',
            [$targetEntry->lft, $targetEntry->rgt, $targetEntry->seminar_id]
        );
    }

    public function storeWith(\SimpleORMap $parent, ForumEntry $entry)
    {
        if ($this->is_new) {
            if ($parent instanceof \JsonApi\Models\ForumCat) {
                $stmt = \DBManager::get()->prepare('INSERT INTO forum_categories_entries
                (category_id, topic_id)
                VALUES (?, ?)');
                $stmt->execute([$parent->id, $this->id]);
                $constraint = $entry;
            } elseif ($parent instanceof \JsonApi\Models\ForumEntry) {
                $constraint = ForumEntry::find($parent->id);
            }

            if (is_null($constraint)) {
                throw new \InvalidArgumentException('There must be a parent');
            }

            \DBManager::get()->exec('UPDATE forum_entries SET lft = lft + 2
                WHERE lft > '.$constraint['rgt']." AND seminar_id = '".$constraint['seminar_id']."'");
            \DBManager::get()->exec('UPDATE forum_entries SET rgt = rgt + 2
                WHERE rgt >= '.$constraint['rgt']." AND seminar_id = '".$constraint['seminar_id']."'");

            $this->lft = $constraint['rgt'];
            $this->rgt = $constraint['rgt'] + 1;
            $this->depth = $constraint['depth'] + 1;

            \NotificationCenter::postNotification('ForumAfterInsert', $this->topic_id, $this->toArray());

            // update "latest_chdate" for easier sorting of actual threads
            \DBManager::get()->exec("UPDATE forum_entries SET latest_chdate = UNIX_TIMESTAMP()
                WHERE topic_id = '".$constraint['topic_id']."'");
        }

        return $this->store();
    }

    public function deleteEntry($topicId)
    {
        $targetEntry = ForumEntry::find($topicId);
        $childEntries = ForumEntry::getChildEntries($topicId);

        //delete an entry and his appended entries...
        $stmt_one = \DBManager::get()->prepare('DELETE FROM forum_entries
            WHERE seminar_id = ? AND lft >= ? AND rgt <= ?');
        $stmt_one->execute([$targetEntry->seminar_id, $targetEntry->lft, $targetEntry->rgt]);

        //...and update affected entries
        $diff = $targetEntry->lft - $targetEntry->rgt;
        $stmt_two = \DBManager::get()->prepare("UPDATE forum_entries SET lft = lft - ${diff}
            WHERE lft > ? AND seminar_id = ?");
        $stmt_two->execute([$targetEntry->rgt, $targetEntry->seminar_id]);

        $stmt_three = \DBManager::get()->prepare("UPDATE forum_entries SET rgt = rgt - ${diff}
            WHERE rgt > ? AND seminar_id = ?");
        $stmt_three->execute([$targetEntry->rgt, $targetEntry->seminar_id]);

        //... delete categories_entries-row if exists

        $stmt_four = \DBManager::get()->prepare('DELETE FROM forum_categories_entries
            WHERE topic_id = ?');
        $stmt_four->bindParam(':ids', $ids, \StudipPDO::PARAM_ARRAY);
        $stmt_four->execute([$topicId]);

        return $stmt_one && $stmt_two && $stmt_three && $stmt_four;
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'forum_entries';

        $config['belongs_to']['category'] = [
            'class_name' => '\JsonApi\Models\ForumEntry',
            'assoc_func' => 'getCatFromEntry',
        ];
        parent::configure($config);
    }
}
