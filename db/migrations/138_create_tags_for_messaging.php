<?php
class CreateTagsForMessaging extends Migration
{
    public function getDescription()
    {
        return _('Converts old messaging-folders into message tags.');
    }
    
    public function up()
    {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `message_tags` (
                  `message_id` varchar(32) NOT NULL,
                  `user_id` varchar(32) NOT NULL,
                  `tag` varchar(64) NOT NULL,
                  `chdate` bigint(20) NOT NULL,
                  `mkdate` bigint(20) NOT NULL,
                  PRIMARY KEY (`message_id`,`user_id`,`tag`)
            ) ENGINE=MyISAM
        ");

        $old_config = DBManager::get()->prepare("
            SELECT user_id, value FROM user_config WHERE field = 'MESSAGING_SETTINGS'
        ");
        $old_config->execute();
        while ($result = $old_config->fetch(PDO::FETCH_ASSOC)) {
            $config = json_decode($result['value'], true);

            //Erstelle alle Tags und verknüpfe sie mit den Nachrichten:
            $statement = DBManager::get()->prepare("
                INSERT IGNORE INTO message_tags (message_id, user_id, tag)
                    SELECT message_user.message_id, message_user.user_id, :tag
                    FROM message_user
                    WHERE message_user.user_id = :user_id
                        AND message_user.folder = :folder_id
                        AND message_user.snd_rec = :snd_rec
            ");
            foreach ((array) $config['folder']['in'] as $folder_id => $tag) {
                if ($tag !== "dummy") {
                    $statement->execute([
                        'tag' => $tag,
                        'user_id' => $result['user_id'],
                        'folder_id' => $folder_id + 1,
                        'snd_rec' => "rec"
                    ]);
                }
            }
            foreach ((array) $config['folder']['out'] as $folder_id => $tag) {
                if ($tag !== "dummy") {
                    $statement->execute([
                        'tag' => $tag,
                        'user_id' => $result['user_id'],
                        'folder_id' => $folder_id + 1,
                        'snd_rec' => "snd"
                    ]);
                }
            }
        }

        DBManager::get()->exec("
            ALTER TABLE `message_user` DROP `folder`
        ");
        DBManager::get()->exec("
          ALTER TABLE `message` DROP `reading_confirmation`;
        ");
    }
    
    public function down()
    {
        $query = "DROP TABLE `message_tags`";
        DBManager::get()->exec($query);
    }
}
