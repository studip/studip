<?php
class AddMissingEvaluationConfiguration extends Migration
{
    public function description()
    {
        return 'Adds missing EVAL_AUSWERTUNG_GRAPH_FORMAT configuration (see biest 11436)';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`,
                    `description`
                  ) VALUES(
                    'EVAL_AUSWERTUNG_GRAPH_FORMAT', 'png', 'string', 'global', 'evaluation',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Das Format, in dem die Diagramme der grafischen Evaluationsauswertung erstellt werden (jpg, png, gif).'
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'EVAL_AUSWERTUNG_GRAPH_FORMAT'";
        DBManager::get()->exec($query);
    }
}
