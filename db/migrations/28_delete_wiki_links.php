<?

/*
 * 28_delete_wiki_links.php - BIEST00253
 *
 * Copyright (C) 2008 - Tobias Thelen (tobias.thelen@uni-osnabrueck.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class DeleteWikiLinks extends Migration
{
    public function description()
    {
        return 'clean up wiki_links table and remove orphaned backlinks';
    }

    public function up ()
    {
        DBManager::get()->exec("DELETE FROM wiki_links
                                USING wiki_links
                                LEFT JOIN wiki ON ( wiki_links.range_id = wiki.range_id
                                                AND wiki_links.from_keyword = wiki.keyword )
                                WHERE wiki.keyword IS NULL");
    }
}
