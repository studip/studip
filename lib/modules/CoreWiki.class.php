<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreWiki implements StudipModule
{
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        if (!Config::get()->WIKI_ENABLE) {
            return null;
        }

        $priviledged = $GLOBALS['perm']->have_studip_perm('tutor', $course_id, $user_id);

        if ($priviledged) {
            $sql = "SELECT COUNT(DISTINCT keyword) AS count_d,
                           COUNT(IF((wiki.chdate > IFNULL(ouv.visitdate, :threshold) AND wiki.user_id != :user_id), keyword, NULL)) AS neue
                    FROM wiki
                    LEFT JOIN object_user_visits AS ouv
                      ON (ouv.object_id = wiki.range_id AND ouv.user_id = :user_id and ouv.type = 'wiki')
                    WHERE wiki.range_id = :course_id
                    GROUP BY wiki.range_id";
        } else {
            $sql = "SELECT COUNT(DISTINCT keyword) AS count_d,
                           COUNT(IF((wiki.chdate > IFNULL(ouv.visitdate, :threshold) AND wiki.user_id != :user_id), keyword, NULL)) AS neue
                    FROM wiki
                    LEFT JOIN wiki_page_config USING (range_id, keyword)
                    LEFT JOIN object_user_visits AS ouv
                      ON (ouv.object_id = wiki.range_id AND ouv.user_id = :user_id and ouv.type = 'wiki')
                    WHERE wiki.range_id = :course_id
                      AND (
                          wiki_page_config.range_id IS NULL
                          OR wiki_page_config.read_restricted = 0
                      )
                    GROUP BY wiki.range_id";
        }
        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', $last_visit);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$result || (!$result['neue'] && !$result['count_d'])) {
            return null;
        }

        $nav = new Navigation(_('Wiki'));
        if ($result['neue']) {
            $nav->setURL('wiki.php', ['view' => 'listnew']);
            $nav->setImage(Icon::create('wiki+new', Icon::ROLE_ATTENTION, [
                'title' => sprintf(
                    ngettext(
                        '%1$d Wiki-Seite, %2$d Änderung(en)',
                        '%1$d Wiki-Seiten, %2$d Änderung(en)',
                        $result['count_d']
                    ),
                    $result['count_d'],
                    $result['neue']
                )
            ]));
            $nav->setBadgeNumber($result['neue']);
        } else {
            $nav->setURL('wiki.php');
            $nav->setImage(Icon::create('wiki', Icon::ROLE_INACTIVE, [
                'title' => sprintf(
                    ngettext(
                        '%d Wiki-Seite',
                        '%d Wiki-Seiten',
                        $result['count_d']
                    ),
                    $result['count_d']
                )
            ]));
        }
        return $nav;
    }

    public function getTabNavigation($course_id)
    {
        if (!Config::get()->WIKI_ENABLE) {
            return null;
        }

        $navigation = new Navigation(_('Wiki'));
        $navigation->setImage(Icon::create('wiki', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('wiki', Icon::ROLE_INFO));

        $keyword = Request::get('keyword');
        if ($keyword != 'WikiWikiWeb') {
            $navigation->addSubNavigation('start', new Navigation(_('Wiki-Startseite'), 'wiki.php?view=show'));
        }
	    if ($keyword) {
             if ($keyword === 'WikiWikiWeb') {
                 $keyword = _('Wiki-Startseite');
             }
            $navigation->addSubNavigation('show', new Navigation(my_substr($keyword, 0, 35), 'wiki.php?view=show', compact('keyword')));
        }
        $navigation->addSubNavigation('listnew', new Navigation(_('Neue Seiten'), 'wiki.php?view=listnew'));
        $navigation->addSubNavigation('listall', new Navigation(_('Alle Seiten'), 'wiki.php?view=listall'));
        return ['wiki' => $navigation];
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata()
    {
        return [
            'summary' => _('Gemeinsames asynchrones Erstellen und Bearbeiten von Texten'),
            'description' => _('Im Wiki-Web oder kurz "Wiki" können '.
                'verschiedene Autor/-innen gemeinsam Texte, Konzepte und andere '.
                'schriftliche Arbeiten erstellen und gestalten, dies '.
                'allerdings nicht gleichzeitig. Texte können individuell '.
                'bearbeitet und die Änderungen gespeichert werden. Das '.
                'Besondere im Wiki ist, dass Studierende und Lehrende '.
                'annähernd die gleichen Rechte (schreiben, lesen, ändern, '.
                'löschen) haben, was sich nicht einschränken lässt. Das '.
                'System erstellt eine Versionshistorie, mit der Änderungen '.
                'nachvollziehbar werden. Einzelne Versionen können zudem '.
                'auch gelöscht werden (nur Lehrende). Ein Export als '.
                'pdf-Datei ist integriert.'),

            'displayname' => _('Wiki-Web'),
            'keywords' => _('Individuelle Bearbeitung von Texten;
                            Versionshistorie;
                            Druckansicht und PDF-Export;
                            Löschfunktion für die aktuellste Seiten-Version;
                            Keine gleichzeitige Bearbeitung desselben Textes möglich, nur nacheinander'),
            'descriptionshort' => _('Gemeinsames asynchrones Erstellen und Bearbeiten von Texten'),
            'descriptionlong' => _('Im Wiki-Web oder kurz "Wiki" können verschiedene Autor/-innen gemeinsam Texte, '.
                                    'Konzepte und andere schriftliche Arbeiten erstellen und gestalten. Dies '.
                                    'allerdings nicht gleichzeitig. Texte können individuell bearbeitet und '.
                                    'gespeichert werden. Das Besondere im Wiki ist, dass Studierende und Lehrende '.
                                    'annähernd die gleichen Rechte (schreiben, lesen, ändern, löschen) haben, was '.
                                    'gegenseitiges Vertrauen voraussetzt. Das System erstellt eine Versionshistorie, '.
                                    'mit der Änderungen nachvollziehbar werden. Einzelne Versionen können zudem auch '.
                                    'gelöscht werden (nur Lehrende). Eine Druckansicht und eine Exportmöglichkeit als '.
                                    'PDF-Datei ist integriert.'),
            'category' => _('Kommunikation und Zusammenarbeit'),
            'icon' => Icon::create('wiki', Icon::ROLE_INFO),
            'screenshots' => [
                'path' => 'plus/screenshots/Wiki-Web',
                'pictures' => [
                    0 => [ 'source' => 'Gemeinsam_erstellte_Texte.jpg', 'title' => 'Gemeinsam erstellte Texte']
                ]
            ]
        ];
    }
}
