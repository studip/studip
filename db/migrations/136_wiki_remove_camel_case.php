<?php
/**
 * 134_wiki_remove_camel_case.php - Enclose wiki links in square brackets.
 *
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */

class WikiRemoveCamelCase extends Migration
{
    function description() {
        return 'Enclose camel-case wiki links in double square brackets.';
    }

    function fixWikiLinks($body) {
        // $camel_case = wiki-links-short in WikiFormat.php before migration
        $camel_case = '\b('
            . '(?:[A-ZÄÖÜ]|&[AOU]uml;)'              // upper-case letter
            . '(?:[a-z\däöüß]|&[aou]uml;|&szlig;)+'  // lower-case letter, or digit
            . '(?:[A-ZÄÖÜ]|&[AOU]uml;)'
            . '(?:[\w\däöüß]|&[aou]uml;|&szlig;)*'   // underscore, digit, lower-case letter
            . ')';
        $open_tag = '(?:\[\[)';
        $close_tag = '(?:(?:\|(?:.*?))?\]\])';       // with optional |text]]
        $wiki_link = "/($open_tag)?$camel_case($close_tag)?/";
        return preg_replace_callback($wiki_link, function($m) use ($open_tag, $close_tag) {
            $has_open = preg_match('/' . $open_tag . '/', $m[1]);
            $has_close = preg_match('/' . $close_tag . '/', $m[3]);
            $is_enclosed = $has_open && $has_close;
            return $is_enclosed ? $m[0] : $m[1] . '[[' . $m[2] . ']]' . $m[3];
        }, $body);
    }

    function up() {
        // fetch all wiki versions
        $stmt = DBManager::get()->prepare('SELECT * FROM wiki');
        $stmt->execute();
        while ($wiki_page = $stmt->fetch(PDO::FETCH_ASSOC)) {
            DBManager::get()->prepare(
                'UPDATE wiki SET body=?'
                . ' WHERE range_id=? AND keyword=? AND version=?'
            )->execute([
                $this->fixWikiLinks($wiki_page['body']),
                $wiki_page['range_id'],
                $wiki_page['keyword'],
                $wiki_page['version']
            ]);
        }
    }

    function down() {
    }
}
