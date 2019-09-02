<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Module_DownloadController extends MVVController
{

    public function details_action($modul_id, $language = null)
    {
        $language = Request::get('display_language', $language);
        ModuleManagementModel::setLanguage($language);

        $modul = Modul::find($modul_id);
        if (!$modul) {
            throw new Exception(_('Ungültiges Modul'));
        }
        $this->getDetails($modul_id, $language);
        $this->download = true;
        $as_pdf = Request::int('pdf');

        $factory = $this->get_template_factory();

        if ($as_pdf) {
            $this->set_content_type('application/pdf');

            $doc = new ExportPDF();

            $template = $factory->open('module/download/pdf');
            $this->set_attributes($template, $modul);

            $doc->addPage();
            $doc->SetFont('helvetica', '', 8);
            $doc->writeHTML($template->render(), false, false, true);

            $this->render_pdf($doc, $modul->getDisplayName() . '.pdf');
        } else {
            $factory = $this->get_template_factory();
            $template = $factory->open('module/download/doc');
            $this->set_attributes($template, $modul);

            $content = $template->render();
            $this->response->add_header('Content-Type', 'application/msword');
            $this->response->add_header('Content-Disposition', 'attachment; '
                    . encode_header_parameter('filename', FileManager::cleanFileName($modul->getDisplayName() . '.doc')));
            $this->render_text($content);
        }
        return;
    }

    private function getDetails($id, $language = null)
    {
        $modul = Modul::find($id);
        if (!$modul) {
            throw new Exception(_('Ungültiges Modul'));
        }

        $type = 1;
        if (count($modul->modulteile) === 1) {
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

        $modulVerantwortung = [];

        foreach ($modul->assigned_users as $users) {
            foreach ($users as $user) {
                if (!isset($modulVerantwortung[$user->gruppe])) {
                    $modulVerantwortung[$user->gruppe] = [
                        'name'  => $GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN']['values'][$user->gruppe]['name'],
                        'users' => []
                    ];
                }
                $modulVerantwortung[$user->gruppe]['users'][$user->user_id] = [
                    'name' => get_fullname($user->user_id),
                    'id'   => $user->user_id
                ];
            }
        }

        $modulTeilData = [];

        foreach ($modul->modulteile as $modulTeil) {
            $deskriptor = $modulTeil->getDeskriptor($language);
            $num_bezeichnung = $GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG']['values'][$modulTeil->num_bezeichnung]['name'];

            $name_kurz = sprintf('%s %d', $num_bezeichnung, $modulTeil->nummer);
            $modulTeilData[$modulTeil->id] = [
                'lernform'               => $GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM']['values'][$modulTeil->lernlehrform]['name'],
                'sws'                    => $modulTeil->sws,
                'name_kurz'              => $name_kurz,
                'bezeichnung'            => $deskriptor->bezeichnung,
                'anteil_note'            => $modulTeil->anteil_note,
                'modulteil'              => $modulTeil->getDisplayName(),
                'kommentar'              => $deskriptor->kommentar,
                'wl_preasenz'            => $modulTeil->wl_praesenz,
                'wl_bereitung'           => $modulTeil->wl_bereitung,
                'wl_selbst'              => $modulTeil->wl_selbst,
                'wl_pruef'               => $modulTeil->wl_pruef,
                'turnus'                 => $GLOBALS['MVV_NAME_SEMESTER']['values'][$modulTeil->semester]['name'],
                'kommentar_wl_preasenz'  => $deskriptor->kommentar_wl_praesenz,
                'kommentar_wl_bereitung' => $deskriptor->kommentar_wl_bereitung,
                'kommentar_wl_selbst'    => $deskriptor->kommentar_wl_selbst,
                'kommentar_wl_pruef'     => $deskriptor->kommentar_wl_pruef,
                'pruef_vorleistung'      => $deskriptor->pruef_vorleistung,
                'pruef_leistung'         => $deskriptor->pruef_leistung,
                'pflicht'                => $modulTeil->pflicht ? _('Ja') : _('Nein'),
                'kommentar_pflicht'      => $deskriptor->kommentar_pflicht,
                'ausgleichbar'           => $modulTeil->ausgleichbar ? _('Ja') : _('Nein'),
                'kapazitaet'             => $modulTeil->kapazitaet,
                'voraussetzung'          => $deskriptor->voraussetzung,
                'kommentar_kapazitaet'   => $deskriptor->kommentar_kapazitaet,
                'lvGruppen'              => []
            ];
            $lvGruppen = Lvgruppe::findByModulteil($modulTeil->id);

            foreach ($lvGruppen as $lvGruppe) {
                $courses = [];
                foreach ($lvGruppe->getAssignedCoursesBySemester($currentSemester->getId()) as $seminar) {
                    $courses[$seminar['seminar_id']] = $seminar;
                }
                $modulTeilData[$modulTeil->id]['lvGruppen'][$lvGruppe->id] = [
                    'courses'   => $courses,
                    'alt_texte' => $lvGruppe->alttext
                ];
            }
        }

        $this->unterrichtssprache = implode(', ', $modul->languages->map(
                function ($al) {
                    return $GLOBALS['MVV_MODUL']['SPRACHE']['values'][$al->lang]['name'];
                }
            ));
        $this->semesterSelector = SemesterData::GetSemesterSelector(null, $currentSemester->getId(), 'semester_id', false);
        $this->modul = $modul;
        $this->pruefungsEbene = $GLOBALS['MVV_MODUL']['PRUEF_EBENE']['values'][$modul->pruef_ebene]['name'];
        $this->modulDeskriptor = $modul->getDeskriptor($language);
        $this->startSemester = SemesterData::getSemesterData($modul->start);
        if ($modul->responsible_institute->institute) {
            $this->instituteName = $modul->responsible_institute->getDisplayName();
        } else {
            $this->instituteName = '';
        }
        $this->modulVerantwortung = $modulVerantwortung;
        $this->modulTeilData = $modulTeilData;
        $this->type = $type;
        $this->modulTeile = $modul->modulteile;
        $this->modulUser = $modul->assigned_users;
        $this->semester = $currentSemester;
        $this->download = (bool)Request::get('download');
        $this->detail_list_url = $this->url_for('modul/detail_list/', $modul->id);
        $this->download_detail_url = $this->url_for('modul/download_detail/', $modul->id);
    }

    private function set_attributes($template, $modul)
    {
        $template->set_attributes(
            [
                'modul'               => $modul,
                'unterrichtssprache'  => $this->unterrichtssprache,
                'semesterSelector'    => $this->semesterSelector,
                'pruefungsEbene'      => $this->pruefungsEbene,
                'modulDeskriptor'     => $this->modulDeskriptor,
                'startSemester'       => $this->startSemester,
                'instituteName'       => $this->instituteName,
                'modulVerantwortung'  => $this->modulVerantwortung,
                'modulTeilKommentar'  => $this->modulTeilKommentar,
                'modulTeilData'       => $this->modulTeilData,
                'type'                => $this->type,
                'modulTeile'          => $this->modulTeile,
                'modulUser'           => $this->modulUser,
                'semester'            => $this->semester,
                'download'            => $this->download,
                'detail_list_url'     => $this->detail_list_url,
                'download_detail_url' => $this->download_detail_url
            ]
        );
    }
}
