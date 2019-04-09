<?php

class Visibilityapi extends Migration {

    function description() {
        return 'Copy visibilitydata from old json compress';
    }

    function up() {
        $sql = "CREATE TABLE IF NOT EXISTS `user_visibility_settings` (
  `user_id` varchar(32)  NOT NULL DEFAULT '',
  `visibilityid` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `category` int(2)  NOT NULL,
  `name` varchar(128)  NOT NULL,
  `state` int(2) NULL,
  `plugin` int(11),
  `identifier` varchar(64)  NOT NULL,
  PRIMARY KEY (`visibilityid`),
  KEY `parent_id` (`parent_id`),
  KEY `identifier` (`identifier`),
  KEY `userid` (`user_id`)
) ENGINE=MyISAM";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $category = ['Studien-/Einrichtungsdaten' => 'studdata',
            'Private Daten' => 'privatedata',
            'Zusätzliche Datenfelder' => 'additionaldata',
            'Eigene Kategorien' => 'owncategory',
            'Allgemeine Daten' => 'commondata'];

        $result = $db->query("SELECT value FROM config WHERE field = 'HOMEPAGE_VISIBILITY_DEFAULT' ORDER BY is_default LIMIT 1");
        $default_visibility = constant($result->fetchColumn());

        $sql = "SELECT `user_id` FROM `auth_user_md5`";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = User::find($result['user_id']);
            Visibility::createDefaultCategories($user->user_id);

            //copy all homepage visibility
            $elements = $user->getHomepageElements();
            if (is_array($elements)) {
                foreach ($elements as $key => $state) {
                    if ($state['visibility'] != $default_visibility) {
                        Visibility::addPrivacySetting($state['name'], $key, $category[$state['category']], 1, $about->auth_user['user_id'], $state['visibility']);
                    }
                }
            }
        }
    }

    function down() {

    }

}

?>
