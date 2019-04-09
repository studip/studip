<?php

class RecalculateScore extends Migration {

    function description() {
        return 'Recalculates the score for all users that have their score published.';
    }

    function up() {

        try {
            $statement = DBManager::get()->prepare("
                ALTER TABLE message ADD INDEX autor_id (autor_id)
            ");
            $statement->execute();
        } catch (PDOException $e) {}

        $statement = DBManager::get()->prepare("
            SELECT user_id FROM user_info WHERE score > 0
        ");
        $statement->execute();
        while ($user_id = $statement->fetch(PDO::FETCH_COLUMN, 0)) {
            self::getScore($user_id);
        }
    }

    function down() {

    }

    /**
     * Retrieves a user's score by aggregating activities from database
     * and writes the result to database and cache.
     *
     * @param string $user_id the user to calculate the score for
     * @return int The given user's score.
     */
    private static function getScore($user_id)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        $cache = StudipCacheFactory::getCache();
        if ($cache->read("user_score_of_".$user_id)) {
            return $cache->read("user_score_of_".$user_id);
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
        $sql = "SELECT ROUND(SUM((-atan(measurement / " . round(31556926 / 1800) . ") / PI() + 0.5) * 200)) AS score
            FROM (
                SELECT ((UNIX_TIMESTAMP() / 1800) - timeslot) / (LN(weigh) + 1) AS measurement
                FROM (
                    SELECT (round(mkdate / 1800)) as timeslot, COUNT(*) AS weigh
                    FROM (" . self::createTimestampQuery() . ") AS mkdates
                    GROUP BY timeslot
                ) AS measurements
            ) AS dates";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute([':user' => $user_id]);
        $score = $stmt->fetchColumn();

        $query = "UPDATE user_info SET score = ? WHERE user_id = ? AND score > 0";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$score, $user_id]);

        $cache->write("user_score_of_".$user_id, $score, 60 * 5);

        return $score;
    }

    private static function createTimestampQuery()
    {
        $statements = [];
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

    private static function getActivityTables()
    {
        $tables = [];
        $tables[] = ['table' => "user_info"];
        $tables[] = ['table' => "comments"];
        $tables[] = ['table' => "dokumente"];
        $tables[] = ['table' => "forum_entries"];
        $tables[] = ['table' => "news"];
        $tables[] = ['table' => "seminar_user"];
        $tables[] = [
            'table' => "blubber",
            'where' => "context_type != 'private'"
        ];
        $tables[] = [
            'table' => "kategorien",
            'user_id_column' => "range_id"
        ];
        $tables[] = [
            'table' => "message",
            'user_id_column' => "autor_id"
        ];
        $tables[] = [
            'table' => "vote",
            'user_id_column' => "range_id"
        ];
        $tables[] = [
            'table' => "voteanswers_user",
            'date_column' => "votedate"
        ];
        $tables[] = [
            'table' => "vote_user",
            'date_column' => "votedate"
        ];
        $tables[] = [
            'table' => "wiki",
            'date_column' => "chdate"
        ];

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

