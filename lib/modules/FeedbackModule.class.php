<?php

/**
 * FeedbackModule.class.php - Feedback Elements for Stud.IP as Module.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nils Gehrke <nils.gehrke@uni-goettingen.de>
 * @license     https://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class FeedbackModule extends CorePlugin implements StudipModule, SystemPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        if (Feedback::hasAdminPerm($course_id)) {
            $navigation = new Navigation(_('Feedback'));
            $navigation->addSubNavigation('index', new Navigation(_('Übersicht'), 'dispatch.php/course/feedback'));
            return ['feedback' => $navigation];
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary'       => _('Einholen von Feedback zu Inhaltselementen'),
            'description'   => _('Ermöglicht das Anlegen von Feedback-Elementen an verschiedenen Inhalten, sodass Nutzer dort ein Feedback zum Inhalt geben können. Dies kann aus einer Punkt- bzw. Sternbewertung bestehen oder nur aus einem Kommentar.'),
            'category'      => _('Kommunikation und Zusammenarbeit'),
            'keywords'      => _('Anlegen von Feedback-Elementen an verschiedenen Stellen; Auswahl verschiedener Feedback-Modi, wie Sternbewertung; Übersicht über alle Feedback-Elemente einer Veranstaltung'),
            'icon'          => Icon::create('star', Icon::ROLE_INFO),
            'screenshots'   => [
                'path'      => 'assets/images/plus/screenshots/Feedback',
                'pictures'      => [
                    ['source' => 'FeedbackIndex.png', 'title' => 'Übersichtsseite des Feedbacktools'],
                    ['source' => 'FeedbackAnDatei.png', 'title' => 'Inhaltselement an einer Datei']
                ]
            ]
        ];
    }

    public function isActivatableForContext(Range $context)
    {
        return $context->getRangeType() === 'course';
    }
}
