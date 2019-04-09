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

    public static function getScoreContent($persons)
    {
        $user_ids = array_keys($persons);

        // News
        $query = "SELECT nr.range_id as user_id, COUNT(*) AS newscount
                  FROM news_range AS nr
                  INNER JOIN news AS n ON (nr.news_id = n.news_id)
                  WHERE nr.range_id IN (?) AND (UNIX_TIMESTAMP() - n.date) <= n.expire
                  GROUP BY nr.range_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $persons[$row['user_id']]['newscount'] = $row['newscount'];
        }

        // Events
        $query = "SELECT range_id as user_id, COUNT(*) AS eventcount
                  FROM calendar_event
                  INNER JOIN event_data ON (calendar_event.event_id = event_data.event_id AND class = 'PUBLIC')
                  WHERE range_id IN (?) AND UNIX_TIMESTAMP() <= end
                  GROUP BY range_id
                  ORDER BY NULL";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_ids]);
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
        $statement->execute([$user_ids]);
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
            $statement->execute([$user_ids]);
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
    public static function getTitel($score, $gender = 0)
    {
        if ($score) {
            $logscore = floor(log10($score) / log10(2));
        } else {
            $logscore = 0;
        }

        if ($logscore > 20) {
            $logscore = 20;
        }

        $titel = [];
        $titel[0]  = [_('Unbeschriebenes Blatt'), _('Unbeschriebenes Blatt')];
        $titel[1]  = [_('Unbeschriebenes Blatt'), _('Unbeschriebenes Blatt')];
        $titel[2]  = [_('Unbeschriebenes Blatt'), _('Unbeschriebenes Blatt')];
        $titel[3]  = [_('Neuling'), _('Neuling')];
        $titel[4]  = [_('Greenhorn'), _('Greenhorn')];
        $titel[5]  = [_('Anfänger'), _('Anfängerin')];
        $titel[6]  = [_('Einsteiger'), _('Einsteigerin')];
        $titel[7]  = [_('Beginner'), _('Beginnerin')];
        $titel[8]  = [_('Novize'), _('Novizin')];
        $titel[9]  = [_('Fortgeschrittener'), _('Fortgeschrittene')];
        $titel[10] = [_('Kenner'), _('Kennerin')];
        $titel[11] = [_('Könner'), _('Könnerin')];
        $titel[12] = [_('Profi'), _('Profi')];
        $titel[13] = [_('Experte'), _('Expertin')];
        $titel[14] = [_('Meister'), _('Meisterin')];
        $titel[15] = [_('Großmeister'), _('Großmeisterin')];
        $titel[16] = [_('Idol'), _('Idol')];
        $titel[17] = [_('Guru'), _('Hohepriesterin')];
        $titel[18] = [_('Lichtgestalt'), _('Lichtgestalt')];
        $titel[19] = [_('Halbgott'), _('Halbgöttin')];
        $titel[20] = [_('Gott'), _('Göttin')];

        return $titel[$logscore][$gender == 2 ? 1 : 0];
    }

    /**
    * Retrieves the score for the current user
    *
    * @return       integer the score
    *
    */
    public static function GetMyScore($user_or_id = null)
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
        $stmt->execute([':user' => $user->id]);
        $score = $stmt->fetchColumn();
        if ($user->score && $user->score != $score) {
            $user->score = $score;
            $user->store();
        }
        $cache->write("user_score_of_{$user->id}", $score, 60 * 5);

        return $score;
    }

    protected static function createTimestampQuery()
    {
        $statements = [];
        foreach (self::getActivityTables() as $table) {
            $statements[] = "SELECT "
                . ($table['date_column'] ?: 'mkdate')
                . " AS mkdate FROM "
                . $table['table']
                . " WHERE "
                . ($table['user_id_column'] ?: 'user_id')
                . " = :user "
                . ($table['where'] ? (' AND ' . $table['where']) : '');
        }
        return join(' UNION ', $statements);
    }

    protected static function getActivityTables()
    {
        $tables = [];
        $tables[] = ['table' => 'user_info'];
        $tables[] = ['table' => 'comments'];
        $tables[] = ['table' => 'file_refs'];
        $tables[] = ['table' => 'forum_entries'];
        $tables[] = ['table' => 'news'];
        $tables[] = ['table' => 'seminar_user'];
        $tables[] = [
            'table' => 'blubber',
            'where' => "context_type != 'private'",
        ];
        $tables[] = [
            'table'          => 'kategorien',
            'user_id_column' => 'range_id',
        ];
        $tables[] = [
            'table'          => 'message',
            'user_id_column' => 'autor_id'
        ];
        $tables[] = ['table' => 'questionnaires'];
        $tables[] = [
            'table'       => 'questionnaire_answers',
            'date_column' => 'chdate',
        ];
        $tables[] = ['table' => 'questionnaire_anonymous_answers'];
        $tables[] = [
            'table'       => 'wiki',
            'date_column' => 'chdate'
        ];

        foreach (PluginManager::getInstance()->getPlugins('ScorePlugin') as $plugin) {
            foreach ((array) $plugin->getPluginActivityTables() as $table) {
                if ($table['table']) {
                    $tables[] = $table;
                }
            }
        }

        return $tables;
    }
}
