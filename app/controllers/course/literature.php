<?php
/**
 * Literaturübersicht von Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Rasmus Fuhse <fuhse@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.1
 */

class Course_LiteratureController extends AuthenticatedController
{
    protected $allow_nobody = true;
    /**
     * Before filter, set up the page by initializing the session and checking
     * all conditions.
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Config::Get()->LITERATURE_ENABLE ) {
            throw new AccessDeniedException(_('Die Literaturübersicht ist nicht aktiviert.'));
        }

        PageLayout::setHelpKeyword('Basis.Literatur');

        checkObject(); // do we have an open object?
        checkObjectModule('literature');
        object_set_visit_module('literature');
    }

    /**
     * Displays a page.
     */
    public function index_action($id = null)
    {
        Navigation::activateItem('/course/literature/view');
        PageLayout::setTitle(Context::getHeaderLine(). " - " . _("Literatur"));
        $this->list = StudipLitList::GetFormattedListsByRange(Context::getId(), object_get_visit(Context::getId(), "literature"));
        $this->_range_id = Context::getId();
    }
}
