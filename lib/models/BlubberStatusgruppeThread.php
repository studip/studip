<?php

class BlubberStatusgruppeThread extends BlubberThread
{
    protected static function configure($config = [])
    {
        $config['belongs_to']['statusgruppe'] = [
            'class_name'  => 'Statusgruppen',
            'foreign_key' => function ($thread) {
                return $thread['metadata']['statusgruppe_id'];
            }
        ];

        parent::configure($config);
    }

    public static function findByStatusgruppe_id($statusgruppe_id)
    {
        $condition = "INNER JOIN statusgruppen ON (statusgruppen.range_id = context_id)
                      WHERE context_type = 'course'
                        AND statusgruppen.statusgruppe_id = :statusgruppe_id
                        AND display_class = 'BlubberStatusgruppeThread'
                        AND metadata LIKE :like";
        $threads = self::findBySQL($condition, [
            'statusgruppe_id' => $statusgruppe_id,
            'like'            => "%{$statusgruppe_id}%"
        ]);
        foreach ($threads as $thread) {
            if ($thread['metadata']['statusgruppe_id'] === $statusgruppe_id) {
                return $thread;
            }
        }
        return false;
    }

    public function isReadable(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        if ($GLOBALS['perm']->have_studip_perm("tutor", $this['context_id'], $user_id)) {
            return true;
        }

        $query = "SELECT 1
                  FROM statusgruppe_user
                  WHERE statusgruppe_id = :statusgruppe_id
                    AND user_id = :user_id";
        return (bool) DBManager::get()->fetchColumn($query, [
            'statusgruppe_id' => $this['metadata']['statusgruppe_id'],
            'user_id'         => $user_id
        ]);
    }

    public function getName()
    {
        $name = sprintf(_('%s in %s'), $this->statusgruppe->name, Course::find($this['context_id'])->name);
        return $name;
    }

    public function getContextTemplate()
    {
        $template = $GLOBALS['template_factory']->open('blubber/coursegroup_context');
        $template->members = $this->statusgruppe->members;
        $template->course = Course::find($this['context_id']);
        $template->thread = $this;
        return $template;
    }

    public function notifyUsersForNewComment($comment)
    {
        $query = "SELECT statusgruppe_user.user_id
                  FROM statusgruppe_user
                  WHERE statusgruppe_id = :statusgruppe_id
                    AND user_id != :me

                  UNION DISTINCT

                  SELECT seminar_user.user_id
                  FROM seminar_user
                  INNER JOIN statusgruppen ON (statusgruppen.range_id = seminar_user.Seminar_id)
                  WHERE statusgruppen.statusgruppe_id = :statusgruppe_id
                    AND seminar_user.status IN ('dozent', 'tutor')
                    AND seminar_user.user_id != :me";
        $user_ids = DBManager::get()->fetchFirst($query, [
            'statusgruppe_id' => $this['metadata']['statusgruppe_id'],
            'me'              => $GLOBALS['user']->id
        ]);

        PersonalNotifications::add(
            $user_ids,
            $this->getURL(),
            sprintf(_('%s hat eine Nachricht geschrieben.'), get_fullname()),
            'blubberthread_' . $this->getId(),
            Icon::create('blubber'),
            true
        );
    }
}
