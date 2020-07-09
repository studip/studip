<?php


/**
 * Settings_LibraryController - Library functionality specific configuration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.6
 */


class Settings_LibraryController extends AuthenticatedController
{
    public function citation_action()
    {
        $this->current_user = User::findCurrent();
        PageLayout::setTitle(_('Zitationsstil einrichten'));

        if (Navigation::hasItem('/profile/settings/citation')) {
            Navigation::activateItem('/profile/settings/citation');
        }
        if (Navigation::hasItem('/course/admin/citation')) {
            Navigation::activateItem('/course/admin/citation');
        }

        $this->config = null;
        $this->selected_citation_style = '';
        if (Context::isCourse()) {
            $this->course = Context::get();
            if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course->id)) {
                throw new AccessDeniedException();
            }
            PageLayout::setTitle(sprintf(
                _('%s: Zitationsstil einrichten'),
                $this->course->getFullName()
            ));
            $this->config = CourseConfig::get($this->course->id);
            $this->selected_citation_style = $this->config->COURSE_LIBRARY_CITATION_STYLE;
        } else {
            $this->user = null;
            $user_id = Request::get('user_id');
            if ($user_id) {
                if (($user_id != $this->current_user->id) && !$GLOBALS['perm']->have_perm('root')) {
                    throw new AccessDeniedException();
                }
                $this->user = User::find($user_id);

                PageLayout::setTitle(sprintf(
                    _('%s: Zitationsstil einrichten'),
                    $this->user->getFullName()
                ));
            } else {
                $this->user = $this->current_user;
            }
            $this->config = UserConfig::get($this->user->id);
            $this->selected_citation_style = $this->config->USER_LIBRARY_CITATION_STYLE;
        }

        $this->available_citation_styles = [];

        $style_directory = $GLOBALS['STUDIP_BASE_PATH']
                         . '/composer/citation-style-language/styles-distribution/';
        if (!is_dir($style_directory)) {
            PageLayout::postError(_('Die Bibliothek mit Zitationsstilen ist nicht installiert!'));
            return;
        }
        $citation_file_names = glob($style_directory . '*.csl');
        if ($citation_file_names === false) {
            PageLayout::postError(_('Die Liste mit verfügbaren Zitationsstilen konnte nicht geladen werden!'));
            return;
        }
        foreach ($citation_file_names as $file_name) {
            $style_name = basename($file_name, '.csl');
            $this->available_citation_styles[] = $style_name;
        }
        sort($this->available_citation_styles);

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->selected_citation_style = Request::get('citation_style');

            if (!in_array($this->selected_citation_style, $this->available_citation_styles)) {
                PageLayout::postError(
                    sprintf(
                        _('Der Zitationsstil "%s" ist nicht verfügbar!'),
                        $this->selected_citation_style
                    )
                );
                return;
            }

            $success = false;
            if (Context::isCourse()) {
                $success = $this->config->store('COURSE_LIBRARY_CITATION_STYLE', $this->selected_citation_style);
            } else {
                $success = $this->config->store('USER_LIBRARY_CITATION_STYLE', $this->selected_citation_style);
            }
            if ($success) {
                PageLayout::postSuccess(_('Der Zitationsstil wurde ausgewählt!'));
            } else {
                PageLayout::postError(_('Fehler beim Speichern des Zitationsstils!'));
            }
        }
    }
}
