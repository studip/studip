<?php

class OERTag extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'oer_tags';
        parent::configure($config);
    }

    public static function findBest($number = 9, $raw = false)
    {
        $statement = DBManager::get()->prepare("
            SELECT oer_tags.*
            FROM (
                SELECT tags.tag_hash, COUNT(*) AS position
                FROM oer_tags_material AS tags
                    INNER JOIN oer_material ON (tags.material_id = oer_material.material_id)
                    LEFT JOIN oer_hosts ON (oer_hosts.host_id = oer_material.host_id)
                WHERE oer_material.draft = '0'
                    AND (oer_material.host_id IS NULL OR oer_hosts.`active` = '1')
                GROUP BY tags.tag_hash
                ) AS best_tags
                INNER JOIN oer_tags ON (best_tags.tag_hash = oer_tags.tag_hash)
            WHERE position > 0
            ORDER BY position DESC
            LIMIT ".(int) $number."
        ");
        $statement->execute();
        $best = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($raw) {
            return $best;
        } else {
            $tags = [];
            foreach ($best as $tag_data) {
                $tags[] = self::buildExisting($tag_data);
            }
            return $tags;
        }
    }

    public static function findRelated($tag_hash, $but_not = [], $limit = 6, $raw = false)
    {
        $statement = DBManager::get()->prepare("
            SELECT oer_tags.*
            FROM (
                SELECT tags1.tag_hash, COUNT(*) AS position
                FROM oer_tags_material AS tags1
                    INNER JOIN oer_tags_material AS tags2 ON (tags1.material_id = tags2.material_id AND tags1.tag_hash != tags2.tag_hash)
                    INNER JOIN oer_material ON (tags1.material_id = oer_material.material_id)
                    LEFT JOIN oer_hosts ON (oer_hosts.host_id = oer_material.host_id)
                WHERE tags2.tag_hash NOT IN (:excluded_tags)
                    AND tags2.tag_hash = :tag_hash
                    AND tags1.tag_hash NOT IN (:excluded_tags)
                    AND oer_material.draft = '0'
                    AND (oer_material.host_id IS NULL OR oer_hosts.`active` = '1')
                GROUP BY tags1.tag_hash
                ) AS best_tags
                INNER JOIN oer_tags ON (best_tags.tag_hash = oer_tags.tag_hash)
            WHERE position > 0
            ORDER BY position DESC
            LIMIT ".(int) $limit."
        ");
        $statement->execute([
            'tag_hash' => $tag_hash,
            'excluded_tags' => $but_not
        ]);
        $best = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($raw) {
            return $best;
        } else {
            $tags = [];
            foreach ($best as $tag_data) {
                $tags[] = self::buildExisting($tag_data);
            }
            return $tags;
        }
    }
}
