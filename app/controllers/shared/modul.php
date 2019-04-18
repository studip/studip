<?php
/**
 * modul.php - Shared_ModulController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */



class Shared_ModulController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        parent::before_filter($action, $args);
    }

    public function overview_action($modul_id, $semester_id = null)
    {
        $display_language = Request::option('display_language', $_SESSION['_language']);
        ModuleManagementModel::setLanguage($display_language);

        $modul = Modul::find($modul_id);
        if (!$modul->hasPublicStatus()) {
            throw new AccessDeniedException();
        }
        if ($modul) {
            $this->details_id = $modul->getId();

            $type = 1;
            if (count($modul->modulteile) == 1) {
                $modulteil = $modul->modulteile->first();
                $type = 3;
                if (count($modulteil->lvgruppen) > 0) {
                    $type = 2;
                }
            } else if (count($modul->modulteile) == 0) {
                $type = 3;
            }

            if (!$semester_id) {
                $semesterSwitch = intval(get_config('SEMESTER_TIME_SWITCH'));
                $currentSemester = SemesterData::getSemesterDataByDate(time() + $semesterSwitch * 7 * 24 * 60 * 60);
            } else {
                $currentSemester = SemesterData::getSemesterData($semester_id);
            }

            $this->modulVerantwortung = [];
            foreach ($modul->assigned_users as $user) {
                $this->modulVerantwortung[$user->gruppe][] = $user;
            }

            $sws = 0;
            $institut = new Institute($modul->responsible_institute->institut_id);
            $modulTeileData = [];
            foreach ($modul->modulteile as $modulTeil) {

                $modulTeilDeskriptor = $modulTeil->getDeskriptor($display_language);

                $sws += (int) $modulTeil->sws;

                $num_bezeichnung = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulTeil->num_bezeichnung]['name'];

                $name_kurz = sprintf('%s %d', $num_bezeichnung, $modulTeil->nummer);

                $modulTeileData[$modulTeil->getId()] = [
                    'name' => $modulTeil->getDisplayName(),
                    'name_kurz' => $name_kurz,
                    'voraussetzung' => $modulTeilDeskriptor->voraussetzung,
                    'pruef_leistung' => $modulTeilDeskriptor->pruef_leistung,
                    'pruef_vorleistung' => $modulTeilDeskriptor->pruef_vorleistung,
                    'kommentar' => $modulTeilDeskriptor->kommentar,
                    'kapazitaet' => $modulTeil->kapazitaet,
                    'lvGruppen' => []
                ];

                $lvGruppen = Lvgruppe::findByModulteil($modulTeil->getId());
                foreach ($lvGruppen as $lvGruppe) {
                    $ids = array_column($lvGruppe->getAssignedCoursesBySemester($currentSemester['semester_id'], $GLOBALS['user']->id), 'seminar_id');
                    $courses = Course::findMany($ids, 'order by Veranstaltungsnummer, Name');
                    $modulTeileData[$modulTeil->getId()]['lvGruppen'][$lvGruppe->getId()] = [
                        'courses' => $courses,
                        'alt_texte' => $lvGruppe->alttext
                    ];
                }
            }
            $this->modulTeile = $modulTeileData;
            $this->deskriptor = $modul->getDeskriptor($display_language);
            $this->institut = $institut;
            $this->semester = $currentSemester;
            $this->sws = $sws;

            $this->pruef_ebene = $GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'][$modul->pruef_ebene]['name'];
            $this->modul = $modul;
            $this->type = $type;
            $this->self_url = $this->url_for('modul/show/' . $id);
            $this->detail_url = $this->url_for('modul/detail/' . $id);
            $this->teilnahmeVoraussetzung = $modul->getDeskriptor()->voraussetzung;
            PageLayout::setTitle($modul->getDisplayName() . ' (' . _('Veranstaltungsübersicht') .')');
        }
    }

    public function description_action($id)
    {
        $modul = Modul::find($id);
        $perm = MvvPerm::get($modul);
        if (!($modul->hasPublicStatus() || $perm->haveObjectPerm(MvvPerm::PERM_READ))) {
            throw new AccessDeniedException();
        }
        $type = 1;
        if (count($modul->modulteile) == 1) {
            $modulteil = $modul->modulteile->first();
            $type = 3;
            if (count($modulteil->lvgruppen) > 0) {
                $type = 2;
            }
        } else if (count($modul->modulteile) == 0) {
            $type = 3;
        }

        if (!Request::get('sem_select')) {
            $currentSemester = Semester::findCurrent();
        } else {
            $currentSemester = Semester::find(Request::get('sem_select'));
        }

        $display_language = Request::get('display_language', $_SESSION['_language']);
        ModuleManagementModel::setLanguage($display_language);

        $this->semesterSelector = SemesterData::GetSemesterSelector(null, $currentSemester['semester_id'], 'semester_id', false);
        $this->modul = $modul;
        $this->pruefungsEbene = $GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'][$modul->pruef_ebene]['name'];
        $this->modulDeskriptor = $modul->getDeskriptor($display_language);
        $this->startSemester = Semester::findByTimestamp($modul->start);
        if ($modul->responsible_institute) {
            if ($modul->responsible_institute->institute) {
                $this->instituteName = $modul->responsible_institute->institute->getValue('name');
            } else {
                $this->instituteName = _('Unbekannte Einrichtung');
            }
        }
        $this->type = $type;
        $this->semester = $currentSemester;
        $this->display_language = $display_language;
        PageLayout::setTitle($modul->getDisplayName() . ' (' . _('Vollständige Modulbeschreibung') .')');
    }

}
