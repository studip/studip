<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreSchedule extends CorePlugin implements StudipModule
{
    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $query = "SELECT COUNT(termin_id) AS count,
                         COUNT(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND autor_id != :user_id), termin_id, NULL)) AS neue
                  FROM (
                      SELECT termin_id, chdate, autor_id
                      FROM termine
                      WHERE range_id = :course_id

                      UNION ALL

                      SELECT termin_id, chdate, autor_id
                      FROM ex_termine
                      WHERE range_id = :course_id
                  ) AS tmp
                  LEFT JOIN object_user_visits AS ouv
                    ON ouv.object_id = :course_id
                       AND ouv.user_id = :user_id
                       AND ouv.plugin_id = :plugin_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', $last_visit);
        $statement->bindValue(':plugin_id', $this->getPluginId());
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result || (!$result['neue'] && !$result['count'])) {
            return null;
        }

        $nav = new Navigation(_('Ablaufplan'), 'dispatch.php/course/dates');
        if ($result['neue']) {
            $nav->setImage(Icon::create('schedule+new', Icon::ROLE_ATTENTION), [
                'title' => sprintf(
                    ngettext(
                        '%1$d Termin, %2$d neuer',
                        '%1$d Termine, %2$d neue',
                        $result['count']
                    ),
                    $result['count'],
                    $result['neue']
                )
            ]);
            $nav->setBadgeNumber($result['neue']);
        } else {
            $nav->setImage(Icon::create('schedule', Icon::ROLE_INACTIVE), [
                'title' => sprintf(
                    ngettext(
                        '%d Termin',
                        '%d Termine',
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
        $navigation = new Navigation(_('Ablaufplan'));
        $navigation->setImage(Icon::create('schedule', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('schedule', Icon::ROLE_INFO));

        $navigation->addSubNavigation('dates', new Navigation(_('Termine'), 'dispatch.php/course/dates'));
        $navigation->addSubNavigation('topics', new Navigation(_('Themen'), 'dispatch.php/course/topics'));

        return ['schedule' => $navigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Anzeige aller Termine der Veranstaltung'),
            'description' => _('Der Ablaufplan listet alle Präsenz-, '.
                'E-Learning-, Klausur-, Exkursions- und sonstige '.
                'Veranstaltungstermine auf. Zur besseren Orientierung und zur '.
                'inhaltlichen Einstimmung der Studierenden können Lehrende den '.
                'Terminen Themen hinzufügen, die z. B. eine Kurzbeschreibung '.
                'der Inhalte darstellen.'),
            'displayname' => _('Ablaufplan'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('Inhaltliche und räumliche Orientierung für Studierende;
                            Beschreibung der Inhalte einzelner Termine;
                            Raumangabe;
                            Themenzuordnung zu Terminen;
                            Terminzuordnung zu Themen'),
            'descriptionshort' => _('Anzeige aller Termine der Veranstaltung, ggf. mit Themenansicht'),
            'descriptionlong' => _('Der Ablaufplan listet alle Präsenz-, E-Learning-, Klausur-, Exkursions- ' .
                                    'und sonstige Veranstaltungstermine auf. Zur besseren Orientierung und zur ' .
                                    'inhaltlichen Einstimmung der Studierenden können Lehrende den Terminen ' .
                                    'Themen hinzufügen, die z. B. eine Kurzbeschreibung der Inhalte darstellen.'),
            'icon' => Icon::create('schedule', Icon::ROLE_INFO),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/Ablaufplan',
                'pictures' => [
                    0 => ['source' => 'Termine_mit_Themen.jpg', 'title' => _('Termine mit Themen')],
                    1 => [ 'source' => 'Thema_bearbeiten_und_einem_Termin_zuordnen.jpg', 'title' => _('Thema bearbeiten und einem Termin zuordnen')]
                ]
            ]
        ];
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
