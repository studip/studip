<?php
class HelpbarTextElement extends WidgetElement
{
    public function __construct($label, $id, $language = null)
    {
        $language = $language ?: $GLOBALS['user']->preferred_language;

        try {
            $query = "SELECT content
                      FROM help_content
                      WHERE content_id = :id AND language = :language
                      ORDER BY version DESC
                      LIMIT 1";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':id', $id);
            $statement->bindValue(':language', $language);
            $statement->execute();
            $text = $statement->fetchColumn() ?: sprintf('Unknown help id "%s"', $id);

            $content = sprintf('<strong>%s</strong><p>%s</p>',
                            htmlReady($label), formatReady($text));
        } catch (Exception $e) {
            if ($GLOBALS['user']->perms === 'root') {
                $content = 'DB-Error: please migrate';
            } else {
                $content = '';
            }
        }

        parent::__construct($content);
    }
}
