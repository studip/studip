<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreParticipants extends CorePlugin implements StudipModule
{
    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $auto_insert_perm = Config::get()->AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM;
        // show the participants-icon only if the course is not an auto-insert-sem
        if (
            AutoInsert::checkSeminar($course_id)
            && (
                ($GLOBALS['perm']->have_perm('admin', $user_id) && !$GLOBALS['perm']->have_perm($auto_insert_perm, $user_id))
                || !$GLOBALS['perm']->have_studip_perm($auto_insert_perm, $course_id, $user_id)
            )
        ) {
            return null;
        }

        $course = Course::find($course_id);

        // Is the participants page hidden for students?
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id, $user_id) && $course->config->COURSE_MEMBERS_HIDE) {
            $tab_navigation = $this->getTabNavigation($course_id);
            if ($tab_navigation && count($tab_navigation['members']->getSubNavigation()) > 0) {
                $sub_nav = $tab_navigation['members']->getSubNavigation();
                $first_nav = reset($sub_nav);

                $navigation = new Navigation($first_nav->getTitle(), $first_nav->getURL());
                $navigation->setImage(Icon::create('persons', Icon::ROLE_INACTIVE));
                return $navigation;

            }
            return null;
        }

        // Determine url to redirect to
        if (!$course->getSemClass()->isGroup()) {
            $url = 'dispatch.php/course/members/index';
        } elseif (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id, $user_id)) {
            return null;
        } else {
            $url = 'dispatch.php/course/grouping/members';
        }

        $navigation = new Navigation(_('Teilnehmende'), $url);
        $navigation->setImage(Icon::create('persons', Icon::ROLE_INACTIVE));

        // Check permission, show no indicator if not at least tutor
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id, $user_id)) {
            return $navigation;
        }

        $query = "SELECT COUNT(tmp.user_id) as count,
                         COUNT(IF((tmp.mkdate > IFNULL(b.visitdate, :threshold) AND tmp.user_id != :user_id), tmp.user_id, NULL)) AS neue
                  FROM (
                      SELECT user_id, mkdate
                      FROM admission_seminar_user
                      WHERE seminar_id = :course_id

                      UNION ALL

                      SELECT user_id, mkdate
                      FROM seminar_user
                      WHERE seminar_id = :course_id
                  ) AS tmp
                  LEFT JOIN object_user_visits AS b
                    ON b.object_id = :course_id
                       AND b.user_id = :user_id
                       AND b.plugin_id = :plugin_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', $last_visit);
        $statement->bindValue(':plugin_id', $this->getPluginId());

        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result['neue']) {
            $navigation->setImage(Icon::create('persons+new', Icon::ROLE_ATTENTION), [
                'title' => sprintf(
                    ngettext(
                        '%1$d Teilnehmende/r, %2$d neue/r',
                        '%1$d Teilnehmende, %2$d neue',
                        $result['count']
                    ),
                    $result['count'],
                    $result['neue']
                )
            ]);
            $navigation->setBadgeNumber($result['neue']);
        } elseif ($result['count']) {
            $navigation->setLinkAttributes([
                'title' => sprintf(
                    ngettext(
                        '%d Teilnehmende/r',
                        '%d Teilnehmende',
                        $result['count']
                    ),
                    $result['count']
                )
            ]);
        }

        return $navigation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        #$navigation = new AutoNavigation(_('Teilnehmende'));
        $navigation = new Navigation(_('Teilnehmende'));
        $navigation->setImage(Icon::create('persons', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('persons', Icon::ROLE_INFO));

        $course = Course::find($course_id);

        // Only courses without children have a regular member list and statusgroups.
        if (!$course->getSemClass()->isGroup()) {
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id) || !$course->config->COURSE_MEMBERS_HIDE) {
                $navigation->addSubNavigation('view', new Navigation(_('Teilnehmende'), 'dispatch.php/course/members'));
            }
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id) || !$course->config->COURSE_MEMBERGROUPS_HIDE) {
                $navigation->addSubNavigation('statusgroups', new Navigation(_('Gruppen'), 'dispatch.php/course/statusgroups'));
            }
        } else {
            if (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                return null;
            }
            $navigation->addSubNavigation('children', new Navigation(_('Teilnehmende in Unterveranstaltungen'), 'dispatch.php/course/grouping/members'));
        }

        if ($course->aux_lock_rule) {
            $navigation->addSubNavigation('additional', new Navigation(_('Zusatzangaben'), 'dispatch.php/course/members/additional'));
        }

        return count($navigation->getSubNavigation()) > 0 ? ['members' => $navigation] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'description' => _('Die Teilnehmenden werden gruppiert nach ihrer '.
                'jeweiligen Funktion in einer Tabelle gelistet. Für Lehrende '.
                'werden sowohl das Anmeldedatum als auch der Studiengang mit '.
                'Semesterangabe dargestellt. Die Liste kann in verschiedene '.
                'Formate exportiert werden. Außerdem gibt es die '.
                'Möglichkeiten, eine Rundmail an alle zu schreiben (nur '.
                'Lehrende) bzw. einzelne Teilnehmende separat anzuschreiben.'),
            'displayname' => _('Teilnehmende'),
            'keywords' => _('Rundmail an einzelne, mehrere oder alle Teilnehmenden;
                            Gruppierung nach Lehrenden, Tutor/-innen und Studierenden (Autor/-innen);
                            Aufnahme neuer Studierender (Autor/-innen) und Tutor/-innen;
                            Import einer Teilnehmendenliste;
                            Export der Teilnehmendenliste;
                            Einrichten von Gruppen;
                            Anzeige Studiengang und Fachsemester'),
            'descriptionshort' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'descriptionlong' => _('Die Teilnehmenden werden gruppiert nach ihrer jeweiligen Rolle in '.
                                   'einer Tabelle gelistet. Für Lehrende werden sowohl das Anmeldedatum '.
                                   'als auch der Studiengang mit Semesterangabe der Studierenden dargestellt. '.
                                   'Die Liste kann in verschiedene Formate exportiert werden. Außerdem gibt '.
                                   'es die Möglichkeiten für Lehrende, allen eine Rundmail zukommen zu lassen '.
                                   'bzw. einzelne Teilnehmende separat anzuschreiben.'),
            'category' => _('Lehr- und Lernorganisation'),
            'icon' => Icon::create('persons', Icon::ROLE_INFO),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/TeilnehmerInnen',
                'pictures' => [
                    ['source' => 'Liste_aller_Teilnehmenden_einer_Veranstaltung.jpg', 'title' => _('Liste aller Teilnehmenden einer Veranstaltung')],
                    ['source' => 'Rundmail_an_alle_TeilnehmerInnen_einer_Veranstaltung.jpg', 'title' => _('Rundmail an alle Teilnehmdenden einer Veranstaltung')],
                ]
            ],
        ];
    }

    protected function getCourseStatus(Course $course, $user_id)
    {
        $member = CourseMember::find([$course->id, $user_id]);
        if ($member) {
            return $member->status;
        }

        if (Config::get()->DEPUTIES_ENABLE && Deputy::isDeputy($user_id, $course->id)) {
            return 'dozent';
        }

        return false;
    }

    public function getInfoTemplate($course_id)
    {
        // TODO: Implement getInfoTemplate() method.
        return null;
    }

    public function isActivatableForContext(Range $context)
    {
        return $context->getRangeType() === 'course';
    }
}
