<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreOverview extends CorePlugin implements StudipModule
{
    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $sql = "SELECT COUNT(nw.news_id) AS count,
                       COUNT(IF((nw.chdate > IFNULL(b.visitdate, :threshold) AND nw.user_id !=:user_id), nw.news_id, NULL)) AS neue
                FROM news_range AS a
                LEFT JOIN news AS nw
                  ON a.news_id = nw.news_id
                     AND UNIX_TIMESTAMP() BETWEEN date AND date + expire
                LEFT JOIN object_user_visits AS b
                  ON b.object_id = a.news_id
                     AND b.user_id = :user_id
                     AND b.plugin_id = :plugin_id
                WHERE a.range_id = :course_id
                GROUP BY a.range_id";

        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->bindValue(':plugin_id', $this->getPluginId());
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return null;
        }

        $nav = new Navigation(_('Ankündigungen'), '');
        if ($result['neue']) {
            $nav->setURL('?new_news=true');
            $nav->setImage(Icon::create('news+new', Icon::ROLE_ATTENTION), [
                'title' => sprintf(
                    ngettext(
                        '%1$d Ankündigung, %2$d neue',
                        '%1$d Ankündigungen, %2$d neue',
                        $result['count']
                    ),
                    $result['count'],
                    $result['neue']
                )
            ]);
            $nav->setBadgeNumber($result['neue']);
        } elseif ($result['count']) {
            $nav->setImage(Icon::create('news', Icon::ROLE_INACTIVE), [
                'title' => sprintf(
                    ngettext(
                        '%d Ankündigung',
                        '%d Ankündigungen',
                        $result['count']
                    ),
                    $result['count']
                )
            ]);
        }
        return $nav;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        $object_type = get_object_type($course_id, ['sem', 'inst']);
        if ($object_type === 'sem') {
            $course = Course::find($course_id);
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$course->status]['class']] ?: SemClass::getDefaultSemClass();
        } else {
            $institute = Institute::find($course_id);
            $sem_class = SemClass::getDefaultInstituteClass($institute->type);
        }

        $navigation = new Navigation(_('Übersicht'));
        $navigation->setImage(Icon::create('seminar', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('seminar', Icon::ROLE_INFO));
        if ($object_type !== 'sem') {
            $navigation->addSubNavigation('info', new Navigation(_('Kurzinfo'), 'dispatch.php/institute/overview'));
            $navigation->addSubNavigation('courses', new Navigation(_('Veranstaltungen'), 'show_bereich.php?level=s&id='.$course_id));
            $navigation->addSubNavigation('schedule', new Navigation(_('Veranstaltungs-Stundenplan'), 'dispatch.php/calendar/instschedule?cid='.$course_id));

            if ($GLOBALS['perm']->have_studip_perm('admin', $course_id)) {
                $navigation->addSubNavigation('admin', new Navigation(_('Administration der Einrichtung'), 'dispatch.php/institute/basicdata/index?new_inst=TRUE'));
            }
        } else {
            $navigation->addSubNavigation('info', new Navigation(_('Kurzinfo'), 'dispatch.php/course/overview'));
            if (!$sem_class['studygroup_mode']) {
                $navigation->addSubNavigation('details', new Navigation(_('Details'), 'dispatch.php/course/details/'));
            }
        }
        return ['main' => $navigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'displayname' => _('Übersicht')
        ];
    }

    public function getInfoTemplate($course_id)
    {
        // TODO: Implement getInfoTemplate() method.
        return null;
    }
}
