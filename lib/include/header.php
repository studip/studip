<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * head line of Stud.IP
 *
 * @author       Stefan Suchi <suchi@data-quest.de>
 * @author       Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @author       Ralf Stockmann <rstockm@gwdg.de>
 * @license      GPL2 or any later version
 * @access       public
 * @modulegroup  visual
 * @module       header.php
 * @package      studip_core
 */

/* ---
 * Mögliche Datenschutz-/Sichtbarkeitsentscheidung: Beim ersten Login wird ein
 * informierender Text mit Entscheidungsmöglichkeit: "Ich will sichtbar sein" oder
 * "Ich will unsichtbar sein" angezeigt.
 *
 * Bei Nutzung dieser Funktion unbedingt die Texte unter locale/de/LC_HELP/visibility_decision.php bzw.
 * locale/en/LC_HELP/visibility_decision.php an die lokalen Verhältnisse anpassen!
 */
if (PageLayout::isHeaderEnabled()) //Einige Seiten benötigen keinen Header, sprich Navigation (Evaluation usw.)
{
    $header_template = $GLOBALS['template_factory']->open('header');
    $header_template->current_page = PageLayout::getTitle();
    $header_template->link_params = array_fill_keys(array_keys(URLHelper::getLinkParams()), NULL);

    if (is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody') {
        // only mark course if user is logged in and free access enabled
        $is_public_course = Context::isCourse() && Config::get()->ENABLE_FREE_ACCESS;
        $is_public_institute = Context::isInstitute()
                            && Config::get()->ENABLE_FREE_ACCESS
                            && Config::get()->ENABLE_FREE_ACCESS != 'courses_only';
        if (($is_public_course || $is_public_institute) &&
            Navigation::hasItem('/course') && Navigation::getItem('/course')->isActive()) {
            // indicate to the template that this course is publicly visible
            // need to handle institutes separately (always visible)
            if (Context::isInstitute()) {
                $header_template->public_hint = _('öffentliche Einrichtung');
            } else if (Course::findCurrent()->lesezugriff == 0) {
                $header_template->public_hint = _('öffentliche Veranstaltung');
            }
        }
        if ($GLOBALS['user']->cfg->ACCESSKEY_ENABLE) {
            $header_template->accesskey_enabled = true;
        }

        if (!$GLOBALS['user']->needsToAcceptTerms()) {
            // fetch semester for quick search box in the link bar
            $semester_data = SemesterData::GetSemesterArray();
            $default_semester = $_SESSION['_default_sem']
                              ? SemesterData::GetSemesterIndexById($_SESSION['_default_sem'])
                              : 'all';
            $header_template->search_semester_nr = $default_semester;
            $header_template->search_semester_name = $default_semester !== 'all'
                                                   ? $semester_data[$default_semester]['name']
                                                   : _('alle Semester');
        }
    }
} else {
    $header_template = $GLOBALS['template_factory']->open('noheader');
}

echo $header_template->render();
