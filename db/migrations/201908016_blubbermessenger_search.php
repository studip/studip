<?php
class BlubbermessengerSearch extends Migration
{
    public function description()
    {
        return "Adds blubber to the global search.";
    }

    public function up()
    {
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM config
            WHERE field = 'GLOBALSEARCH_MODULES'
        ");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC);
        $config['value'] = json_decode($config['value'], true);
        $config['value']['GlobalSearchBlubber'] = [
            'order' => 13,
            'active' => true,
            'fulltext' => true
        ];

        $statement = DBManager::get()->prepare("
            UPDATE config
            SET `value` = :json
            WHERE field = 'GLOBALSEARCH_MODULES'
        ");
        $statement->execute([
            'json' => json_encode($config['value'])
        ]);

        $statement = DBManager::get()->prepare("
            SELECT *
            FROM config_values
            WHERE field = 'GLOBALSEARCH_MODULES'
        ");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC);
        if ($config) {
            $config['value'] = json_decode($config['value'], true);
            $config['value']['GlobalSearchBlubber'] = [
                'order' => 13,
                'active' => true,
                'fulltext' => true
            ];

            $statement = DBManager::get()->prepare("
                UPDATE config_values
                SET `value` = :json
                WHERE field = 'GLOBALSEARCH_MODULES'
            ");
            $statement->execute([
                'json' => json_encode($config['value'])
            ]);
        }
    }

    public function down()
    {
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM config_values
            WHERE field = 'GLOBALSEARCH_MODULES'
        ");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC);
        if ($config) {
            $config['value'] = json_decode($config['value'], true);
            unset($config['value']['GlobalSearchBlubber']);
            $statement = DBManager::get()->prepare("
                UPDATE config_values
                SET `value` = :json
                WHERE field = 'GLOBALSEARCH_MODULES'
            ");
            $statement->execute([
                'json' => json_encode($config['value'])
            ]);
        }

        $statement = DBManager::get()->prepare("
            SELECT *
            FROM config
            WHERE field = 'GLOBALSEARCH_MODULES'
        ");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC);
        $config['value'] = json_decode($config['value'], true);
        unset($config['value']['GlobalSearchBlubber']);
        $statement = DBManager::get()->prepare("
            UPDATE config
            SET `value` = :json
            WHERE field = 'GLOBALSEARCH_MODULES'
        ");
        $statement->execute([
            'json' => json_encode($config['value'])
        ]);

    }
}
