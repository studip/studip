<?php
# Lifter002: TEST
# Lifter007: TEST

/**
 * admin_search_form.inc.php - Suche fuer die Verwaltungsseiten von Stud.IP.
 *
 * @author Stefan Suchi <suchi@gmx.de>
 * @author Ralf Stockmann <rstockm@gwdg.de>
 * @author Cornelis Kater <ckater@gwdg.de>
 * @copyright 2001
 * @license GPL2 or any later version
 */

if (!Institute::findCurrent()) {
    $template = $GLOBALS['template_factory']->open('admin/institute_search.php');
    $template->set_layout('layouts/base.php');
    $template->institutes = Institute::getMyInstitutes($GLOBALS['user']->id);
    echo $template->render();

    page_close();
    die;
}
