<?php
# Lifert001: TODO
# Lifter002: TEST
# Lifter003: TEST
# Lifter005: DONE - not applicable
# Lifter007: TEST
# Lifter010: DONE - not applicable

/**
 * eval_config.php
 *
 * Konfiurationsseite fuer Eval-Auswertungen
 *
 *
 * @author Jan Kulmann <jankul@tzi.de>
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// eval_config.php
// Copyright (C) 2005 Jan Kulmann <jankul@tzi.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

    require '../lib/bootstrap.php';

    page_open([
        'sess' => 'Seminar_Session',
        'auth' => 'Seminar_Auth',
        'perm' => 'Seminar_Perm',
        'user' => 'Seminar_User'
    ]);

    $perm->check('user');

    include 'lib/seminar_open.php';             // initialise Stud.IP-Session

    // -- here you have to put initialisations for the current page
    require_once 'lib/evaluation/evaluation.config.php';
    require_once EVAL_FILE_EVAL;
    require_once EVAL_FILE_OBJECTDB;

    // Start of Output
    PageLayout::setTitle(_('Evaluations-Auswertung'));
    PageLayout::setHelpKeyword('Basis.Evaluationen');
    Navigation::activateItem('/tools/evaluation');

    // Extract variables from request
    $eval_id     = Request::option('eval_id');
    $template_id = Request::option('template_id');

    // Überprüfen, ob die Evaluation existiert oder der Benutzer genügend Rechte hat
    $eval = new Evaluation($eval_id);
    $eval->check();
    if (EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval) == YES || count($eval->errorArray) > 0) {
        throw new Exception(_("Diese Evaluation ist nicht vorhanden oder Sie haben nicht ausreichend Rechte!"));
    }

    // Store settings
    if (Request::submitted('store')) {
        if (!$template_id) {
            $template_id = DbView::get_uniqid();

            $query = "INSERT INTO eval_templates_eval (eval_id, template_id)
                      VALUES (?, ?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$eval_id, $template_id]);
        }

        $show_questions              = Request::int('show_questions');
        $show_total_stats            = Request::int('show_total_stats');
        $show_graphics               = Request::int('show_graphics');
        $show_questionblock_headline = Request::int('show_questionblock_headline');
        $show_group_headline         = Request::int('show_group_headline');

        $polscale_gfx_type           = Request::option('polscale_gfx_type');
        $likertscale_gfx_type        = Request::option('likertscale_gfx_type');
        $mchoice_scale_gfx_type      = Request::option('mchoice_scale_gfx_type');

        $query = "INSERT INTO eval_templates
                      (template_id, user_id, name,
                       show_questions, show_total_stats, show_graphics,
                       show_questionblock_headline, show_group_headline,
                       polscale_gfx_type, likertscale_gfx_type, mchoice_scale_gfx_type)
                  VALUES (?, ?, 'nix', ?, ?, ?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE show_questions = VALUES(show_questions),
                                          show_total_stats = VALUES(show_total_stats),
                                          show_graphics = VALUES(show_graphics),
                                          show_questionblock_headline = VALUES(show_questionblock_headline),
                                          show_group_headline = VALUES(show_group_headline),
                                          polscale_gfx_type = VALUES(polscale_gfx_type),
                                          likertscale_gfx_type = VALUES(likertscale_gfx_type),
                                          mchoice_scale_gfx_type = VALUES(mchoice_scale_gfx_type)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $template_id, $GLOBALS['user']->id,
            $show_questions, $show_total_stats, $show_graphics,
            $show_questionblock_headline, $show_group_headline,
            $polscale_gfx_type, $likertscale_gfx_type, $mchoice_scale_gfx_type
        ]);

        PageLayout::postMessage(MessageBox::success(_('Die Auswertungskonfiguration wurde gespeichert.')));
    }

    // Read template setting from db
    $query = "SELECT template_id,
                     show_total_stats, show_graphics, show_questions, show_group_headline, show_questionblock_headline,
                     polscale_gfx_type, likertscale_gfx_type, mchoice_scale_gfx_type
              FROM eval_templates AS t
              JOIN eval_templates_eval AS te USING (template_id)
              WHERE te.eval_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$eval_id]);
    $templates = $statement->fetch(PDO::FETCH_ASSOC);

    // Open, populate and render template
    $template = $GLOBALS['template_factory']->open('evaluation/config');
    $template->set_layout($GLOBALS['template_factory']->open('layouts/base'));

    $template->eval_id      = $eval_id;
    $template->templates    = $templates;
    $template->has_template = !empty($templates);

    echo $template->render();

  // Save data back to database.
  page_close();
