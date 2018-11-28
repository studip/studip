<?
/**
 * Score.class.php - Score class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Ralf Stockmann <rstockm@gwdg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class Score
{
    // How long is the duration of a score-block?
    const MEASURING_STEP = 1800; // half an hour

    static function getScoreContent($persons)
    {
        $user_ids = array_keys($persons);

        // News
        $query = "SELECT nr.range_id as user_id, COUNT(*) AS newscount
                  FROM news_range AS nr
                  INNER JOIN news AS n ON (nr.news_id = n.news_id)
                  WHERE nr.range_id IN (?) AND (? - n.date) <= n.expire
                  GROUP BY nr.range_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user_ids, gmmktime()));
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $persons[$row['user_id']]['newscount'] = $row['newscount'];
        }

        // Events
        $query = "SELECT range_id as user_id, COUNT(*) AS eventcount
                  FROM calendar_event
                  INNER JOIN event_data ON (calendar_event.event_id = event_data.event_id AND class = 'PUBLIC')
                  WHERE range_id IN (?) AND ? <= end
                  GROUP BY range_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user_ids, gmmktime()));
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $persons[$row['user_id']]['eventcount'] = $row['eventcount'];
        }

        // Literature
        $query = "SELECT range_id as user_id, COUNT(*) AS litcount
                  FROM lit_list
                  INNER JOIN lit_list_content USING (list_id)
                  WHERE range_id IN (?) AND visibility = 1
                  GROUP BY range_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user_ids));
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $persons[$row['user_id']]['litcount'] = $row['litcount'];
        }

        // Votes
        if (get_config('VOTE_ENABLE')){
            $query = "SELECT questionnaire_assignments.range_id as user_id, COUNT(*) AS votecount
                      FROM questionnaire_assignments
                      WHERE questionnaire_assignments.range_id IN (?)
                          AND questionnaire_assignments.range_type = 'user'
                      GROUP BY questionnaire_assignments.range_id
                      ORDER BY NULL";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($user_ids));
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $persons[$row['user_id']]['votecount'] = $row['votecount'];
            }
        }

        return $persons;
    }

    /**
    * Retrieves the titel for a given studip score
    *
    * @param        integer a score value
    * @param        integer gender (0: unknown, 1: male; 2: female)
    * @return       string  the titel
    *
    */
    static function getTitel($score, $gender = 0)
    {
        if ($score)
            $logscore = floor(log10($score) / log10(2));
        else
            $logscore = 0;

        if ($logscore > 20)
            $logscore = 20;

        $titel[0]  =    array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
        $titel[1]  =    array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
        $titel[2]  =    array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
        $titel[3]  =    array(0 => _("Neuling"), 1 => _("Neuling"));
        $titel[4]  =    array(0 => _("Greenhorn"), 1 => _("Greenhorn"));
        $titel[5]  =    array(0 => _("Anfänger"), 1 => _("Anfängerin"));
        $titel[6]  =    array(0 => _("Einsteiger"), 1 => _("Einsteigerin"));
        $titel[7]  =    array(0 => _("Beginner"), 1 => _("Beginnerin"));
        $titel[8]  =    array(0 => _("Novize"), 1 => _("Novizin"));
        $titel[9]  =    array(0 => _("Fortgeschrittener"), 1 => _("Fortgeschrittene"));
        $titel[10] =    array(0 => _("Kenner"), 1 => _("Kennerin"));
        $titel[11] =    array(0 => _("Könner"), 1 => _("Könnerin"));
        $titel[12] =    array(0 => _("Profi"), 1 => _("Profi"));
        $titel[13] =    array(0 => _("Experte"), 1 => _("Expertin"));
        $titel[14] =    array(0 => _("Meister"), 1 => _("Meisterin"));
        $titel[15] =    array(0 => _("Großmeister"), 1 => _("Großmeisterin"));
        $titel[16] =    array(0 => _("Idol"), 1 => _("Idol"));
        $titel[17] =    array(0 => _("Guru"), 1 => _("Hohepriesterin"));
        $titel[18] =    array(0 => _("Lichtgestalt"), 1 => _("Lichtgestalt"));
        $titel[19] =    array(0 => _("Halbgott"), 1 => _("Halbgöttin"));
        $titel[20] =    array(0 => _("Gott"), 1 => _("Göttin"));

        return $titel[$logscore][$gender == 2 ? 1 : 0];
    }

    /**
    * Retrieves the score for the current user
    *
    * @return       integer the score
    *
    */
    static function GetMyScore($user_or_id = null)
    {
        $user = $user_or_id ? User::toObject($user_or_id) : User::findCurrent();
        $cache = StudipCacheFactory::getCache();
        if ($cache->read("user_score_of_".$user->id)) {
            return $cache->read("user_score_of_".$user->id);
        }
        //Behold! The all new mighty score algorithm!
        //Step 1: Select all activities as mkdate-timestamps.
        //Step 2: Group these activities to timeslots of halfhours
        //        with COUNT(*) as a weigh of the timeslot.
        //Step 3: Calculate the measurement of the timeslot from the weigh of it.
        //        This makes the first activity count fully, the second
        //        almost half and so on. We use log_n to make huge amounts of
        //        activities to not count so much.
        //Step 4: Calculate a single score for each timeslot depending on the
        //        measurement and the mkdate-timestamp. Use arctan as the function
        //        here so that older activities tend to zero.
        //Step 5: Sum all scores from all timeslots together.
        $sql = "
            SELECT round(SUM((-atan(measurement / " . round(31556926 / self::MEASURING_STEP) . ") / PI() + 0.5) * 200)) as score
            FROM (
                SELECT ((unix_timestamp() / " . self::MEASURING_STEP . ") - timeslot) / (LN(weigh) + 1) AS measurement
                FROM (
                    SELECT (round(mkdate / " . self::MEASURING_STEP . ")) as timeslot, COUNT(*) AS weigh
                    FROM (
                        " . self::createTimestampQuery() . "
                    ) as mkdates
                    GROUP BY timeslot
                ) as measurements
            ) as dates
        ";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array(':user' => $user->id));
        $score = $stmt->fetchColumn();
        if ($user->score && $user->score != $score) {
            $user->score = $score;
            $user->store();
        }
        $cache->write("user_score_of_".$user->id, $score, 60 * 5);

        return $score;
    }

    static protected function createTimestampQuery() {
        $statements = array();
        foreach (self::getActivityTables() as $table) {
            $statements[] = "SELECT "
                . ($table['date_column'] ? : 'mkdate')
                . " AS mkdate FROM "
                . $table['table']
                . " WHERE "
                . ($table['user_id_column'] ? : 'user_id')
                . " = :user "
                . ($table['where'] ? (' AND ' . $table['where']) : '');
        }
        return join(' UNION ', $statements);
    }

    static protected function getActivityTables() {
        $tables = array();
        $tables[] = array('table' => "user_info");
        $tables[] = array('table' => "comments");
        $tables[] = array('table' => "file_refs");
        $tables[] = array('table' => "forum_entries");
        $tables[] = array('table' => "news");
        $tables[] = array('table' => "seminar_user");
        $tables[] = array(
            'table' => "blubber",
            'where' => "context_type != 'private'"
        );
        $tables[] = array(
            'table' => "kategorien",
            'user_id_column' => "range_id"
        );
        $tables[] = array(
            'table' => "message",
            'user_id_column' => "autor_id"
        );
        $tables[] = array(
            'table' => "questionnaires"
        );
        $tables[] = array(
            'table' => "questionnaire_answers",
            'date_column' => "chdate"
        );
        $tables[] = array(
            'table' => "questionnaire_anonymous_answers"
        );
        $tables[] = array(
            'table' => "wiki",
            'date_column' => "chdate"
        );

        foreach (PluginManager::getInstance()->getPlugins("ScorePlugin") as $plugin) {
            foreach ((array) $plugin->getPluginActivityTables() as $table) {
                if ($table['table']) {
                    $tables[] = $table;
                }
            }
        }

        return $tables;
    }
}
