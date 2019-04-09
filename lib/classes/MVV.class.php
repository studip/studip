<?php
/**
 * MVV.class.php
 * Helper class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */


require_once 'config/mvv_config.php';

class MVV implements Loggable {

    /**
     * The global key used by mvv classes to store values in cache.
     */
    const CACHE_KEY = 'MVV';

    /**
     * Determines whether the mvv backend is visible.
     * 
     * @return boolean True if backend is visible
     */
    public static function isVisible() {
        if (!$GLOBALS['perm']) {
            return false;
        }
        if ($GLOBALS['perm']->have_perm('root') || ($GLOBALS['perm']->have_perm('admin')
                && RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVAdmin'))) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVEntwickler')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVRedakteur')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVTranslator')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVFreigabe')) {
            return true;
        }
        if (RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVLvGruppenAdmin')) {
            return true;
        }
        return false;
    }

    /**
     * Determines whether the search for modules is visible in the global search.
     * 
     * @return boolean True if backend is visible in search
     */
    public static function isVisibleSearch()
    {
        return ($GLOBALS['perm']->have_perm('autor') || Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY)
                && Modul::publicModulesAvailable();
    }

    /**
     * This method enriches the logentry templates with data of the mvv classes
     *
     * @param  LogEvent  log event entry
     *
     * @return void
     */
    public static function logFormat(LogEvent $event)
    {
        $templ = $event->action->info_template;

        $table = explode('.', $event->info);

        switch ($table[0]) {

            case 'abschluss':
                $abschluss = Abschluss::find($event->affected_range_id);
                if ($abschluss) {
                    $url = URLHelper::getURL('dispatch.php/fachabschluss/abschluesse/details/' . $abschluss->getId(), [], true);
                    $templ = str_replace('%abschluss(%affected)', '<a href="' . $url . '">' . htmlReady($abschluss->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_abschl_kategorie':
                $abskategorie = AbschlussKategorie::find($event->affected_range_id);
                if ($abskategorie) {
                    $url = URLHelper::getURL('dispatch.php/fachabschluss/kategorien/details/' . $abskategorie->getId(), [], true);
                    $templ = str_replace('%abskategorie(%affected)', '<a href="' . $url . '">' . htmlReady($abskategorie->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_abschl_zuord':
                $abschluss = Abschluss::find($event->affected_range_id);
                if ($abschluss) {
                    $url = URLHelper::getURL('dispatch.php/fachabschluss/abschluesse/details/' . $abschluss->getId(), [], true);
                    $templ = str_replace('%abschluss(%affected)', '<a href="' . $url . '">' . htmlReady($abschluss->getDisplayName()) . '</a>', $templ);
                }
                $co_kategorie = AbschlussKategorie::find($event->coaffected_range_id);
                if ($co_kategorie) {
                    $url = URLHelper::getURL('dispatch.php/fachabschluss/kategorien/details/' . $co_kategorie->getId(), [], true);
                    $templ = str_replace('%abskategorie(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_kategorie->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_dokument':
            case 'mvv_dokument_zuord':
                $dokument = MvvDokument::find($event->affected_range_id);
                if ($dokument) {
                    $url = URLHelper::getURL('dispatch.php/materialien/dokumente/details/' . $dokument->getId(), [], true);
                    $templ = str_replace('%dokument(%affected)', '<a href="' . $url . '">' . htmlReady($dokument->getDisplayName()) . '</a>', $templ);
                    if ($event->coaffected_range_id) {
                        $mmv_object = call_user_func([$event->dbg_info, 'find'], $event->coaffected_range_id);
                        if ($mmv_object) {
                            $templ = str_replace('%object_type(%coaffected)', 'in ' . htmlReady($mmv_object->getDisplayName()), $templ);
                        } else {
                            $templ = str_replace('%object_type(%coaffected)', '', $templ);
                        }
                    }
                }
                break;

            case 'fach':
            case 'mvv_fach_inst':
                $fach = Fach::find($event->affected_range_id);
                if ($fach) {
                    $url = URLHelper::getURL('dispatch.php/fachabschluss/faecher/details/' . $fach->getId(), [], true);
                    $templ = str_replace('%fach(%affected)', '<a href="' . $url . '">' . htmlReady($fach->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_lvgruppe':
            case 'mvv_lvgruppe_seminar':
                $lvgruppe = Lvgruppe::find($event->affected_range_id);
                if ($lvgruppe) {
                    $url = URLHelper::getURL('dispatch.php/lvgruppen/lvgruppen/details/' . $lvgruppe->getId(), [], true);
                    $templ = str_replace('%lvgruppe(%affected)', '<a href="' . $url . '">' . htmlReady($lvgruppe->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_lvgruppe_modulteil':
                $lvgruppe = Lvgruppe::find($event->affected_range_id);
                if ($lvgruppe) {
                    $url = URLHelper::getURL('dispatch.php/lvgruppen/lvgruppen/details/' . $lvgruppe->getId(), [], true);
                    $templ = str_replace('%lv(%affected)', '<a href="' . $url . '">' . htmlReady($lvgruppe->getDisplayName()) . '</a>', $templ);
                }
                $co_modulteil = Modulteil::find($event->coaffected_range_id);
                if ($co_modulteil) {
                    $url = URLHelper::getURL('dispatch.php/module/module/modulteil_lvg/' . $co_modulteil->getId(), [], true);
                    $templ = str_replace('%modulteil(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_modulteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modul':
            case 'mvv_modul_user':
            case 'mvv_modul_inst':
                $modul = Modul::find($event->affected_range_id);
                if ($modul) {
                    $url = URLHelper::getURL('dispatch.php/module/module/details/' . $modul->getId(), [], true);
                    $templ = str_replace('%modul(%affected)', '<a href="' . $url . '">' . htmlReady($modul->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modulteil':
                $modulteil = Modulteil::find($event->affected_range_id);
                if ($modulteil) {
                    $url = URLHelper::getURL('dispatch.php/module/module/modulteil_lvg/' . $modulteil->getId(), [], true);
                    $templ = str_replace('%modulteil(%affected)', '<a href="' . $url . '">' . htmlReady($modulteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modulteil_deskriptor':
                $modulteil_desk = ModulteilDeskriptor::find($event->affected_range_id);
                if ($modulteil_desk) {
                    $modteil = Modulteil::find($modulteil_desk->modulteil_id);
                    $url = URLHelper::getURL('dispatch.php/module/module/modulteil_lvg/' . $modteil->getId(), [], true);
                    $templ = str_replace('%modulteildesk(%affected)', 'in Modulteil <a href="' . $url . '">' . htmlReady($modteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modulteil_language':
                $modulteil = Modulteil::find($event->affected_range_id);
                if ($modulteil) {
                    $url = URLHelper::getURL('dispatch.php/module/module/modulteil_lvg/' . $modulteil->getId(), [], true);
                    $templ = str_replace('%modulteil(%affected)', '<a href="' . $url . '">' . htmlReady($modulteil->getDisplayName()) . '</a>', $templ);
                }
                $co_mtlanguage = ModulteilLanguage::find([
                    $event->affected_range_id,
                    $event->coaffected_range_id
                ]);
                if ($co_mtlanguage) {
                    $templ = str_replace('%language(%coaffected)', htmlReady($co_mtlanguage->getDisplayName()), $templ);
                }
                break;

            case 'mvv_modulteil_stgteilabschnitt':
                $modulteil = Modulteil::find($event->affected_range_id);
                if ($modulteil) {
                    $url = URLHelper::getURL('dispatch.php/module/module/modulteil_lvg/' . $modulteil->getId(), [], true);
                    $templ = str_replace('%modulteil(%affected)', '<a href="' . $url . '">' . htmlReady($modulteil->getDisplayName()) . '</a>', $templ);
                }
                $co_stgteilabs = StgteilAbschnitt::find($event->coaffected_range_id);
                if ($co_stgteilabs) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/versionen/details_abschnitt/' . $co_stgteilabs->getId(), [], true);
                    $templ = str_replace('%stgteilabs(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_stgteilabs->getDisplayName()) . '</a>', $templ);
                    $templ = str_replace('%fachsem', $event->dbg_info, $templ);
                }
                break;

            case 'mvv_modul_deskriptor':
                $modul_desk = ModulDeskriptor::find($event->affected_range_id);
                if ($modul_desk) {
                    $mod = Modul::find($modul_desk->modul_id);
                    $url = URLHelper::getURL('dispatch.php/module/module/details/' . $mod->getId(), [], true);
                    $templ = str_replace('%moduldesk(%affected)', 'in Modul <a href="' . $url . '">' . htmlReady($mod->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_modul_language':
                $modul = Modul::find($event->affected_range_id);
                if ($modul) {
                    $url = URLHelper::getURL('dispatch.php/module/module/details/' . $modul->getId(), [], true);
                    $templ = str_replace('%modul(%affected)', '<a href="' . $url . '">' . htmlReady($modul->getDisplayName()) . '</a>', $templ);
                }
                $co_mlanguage = ModulLanguage::find([
                    $event->affected_range_id,
                    $event->coaffected_range_id
                ]);
                if ($co_mlanguage) {
                    $templ = str_replace('%language(%coaffected)', htmlReady($co_mlanguage->getDisplayName()), $templ);
                }
                break;

            case 'mvv_stgteil':
            case 'mvv_fachberater':
                $stgteil = StudiengangTeil::find($event->affected_range_id);
                if ($stgteil) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/studiengangteile/details_versionen/' . $stgteil->getId(), [], true);
                    $templ = str_replace('%stgteil(%affected)', '<a href="' . $url . '">' . htmlReady($stgteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteilabschnitt':
                $stgteilabs = StgteilAbschnitt::find($event->affected_range_id);
                if ($stgteilabs) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/versionen/details_abschnitt/' . $stgteilabs->getId(), [], true);
                    $templ = str_replace('%stgteilabs(%affected)', '<a href="' . $url . '">' . htmlReady($stgteilabs->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteilabschnitt_modul':
                $stgteilabs = StgteilAbschnitt::find($event->affected_range_id);
                if ($stgteilabs) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/versionen/details_abschnitt/' . $stgteilabs->getId(), [], true);
                    $templ = str_replace('%stgteilabs(%affected)', '<a href="' . $url . '">' . htmlReady($stgteilabs->getDisplayName()) . '</a>', $templ);
                }
                $co_modul = Modul::find($event->coaffected_range_id);
                if ($co_modul) {
                    $url = URLHelper::getURL('dispatch.php/module/module/details/' . $co_modul->getId(), [], true);
                    $templ = str_replace('%modul(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_modul->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteilversion':
                $version = StgteilVersion::find($event->affected_range_id);
                if ($version) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/versionen/abschnitte/' . $version->getId(), [], true);
                    $templ = str_replace('%version(%affected)', '<a href="' . $url . '">' . htmlReady($version->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stgteil_bez':
                $stgteilbez = StgteilBezeichnung::find($event->affected_range_id);
                if ($stgteilbez) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/stgteilbezeichnungen/details/' . $stgteilbez->getId(), [], true);
                    $templ = str_replace('%stgteilbez(%affected)', '<a href="' . $url . '">' . htmlReady($stgteilbez->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_stg_stgteil':
                $stg = Studiengang::find($event->affected_range_id);
                if ($stg) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/studiengaenge/details_studiengang/' . $stg->getId(), [], true);
                    $templ = str_replace('%stg(%affected)', '<a href="' . $url . '">' . htmlReady($stg->getDisplayName()) . '</a>', $templ);
                }
                $co_stgteil = StudiengangTeil::find($event->coaffected_range_id);
                if ($co_stgteil) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/studiengangteile/details_versionen/' . $co_stgteil->getId(), [], true);
                    $templ = str_replace('%stgteil(%coaffected)', '<a href="' . $url . '">' . htmlReady($co_stgteil->getDisplayName()) . '</a>', $templ);
                }
                break;

            case 'mvv_studiengang':
                $stg = Studiengang::find($event->affected_range_id);
                if ($stg) {
                    $url = URLHelper::getURL('dispatch.php/studiengaenge/studiengaenge/details_studiengang/' . $stg->getId(), [], true);
                    $templ = str_replace('%stg(%affected)', '<a href="' . $url . '">' . htmlReady($stg->getDisplayName()) . '</a>', $templ);
                }
                break;

            default:
                break;
        }

        // specials
        // Benutzergruppen im Modul z.B. Modulverantwortlicher
        $templ = str_replace('%gruppe', $event->dbg_info, $templ);
        // Objekt konnte nicht eingesetzt werden da es vermutlich nicht mehr existiert, beim delete landet der alte Bezeichner im debug
        $templ = str_replace('%dokument(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%modul(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%modulteil(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%modulteildesk(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%moduldesk(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stg(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteil(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteilabs(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%version(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteilbez(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%lvgruppe(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%fach(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%abschluss(%affected)', $event->dbg_info, $templ);
        $templ = str_replace('%abskategorie(%affected)', $event->dbg_info, $templ);

        $templ = str_replace('%abskategorie(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%modulteil(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%modul(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%language(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteilabs(%coaffected)', $event->dbg_info, $templ);
        $templ = str_replace('%stgteil(%coaffected)', $event->dbg_info, $templ);

        return $templ;
    }


    /**
     * This method searches the log-entries for log-actions of the mvv classes.
     * Used by search function on log page.
     *
     * @param  string  $needle The search term.
     * @param  string  $action_name The name of the log action.
     *
     * @return array Found log events.
     */
    public static function logSearch($needle, $action_name = null)
    {
        $result = [];
        $sql_needle = DBManager::get()->quote($needle);

        $modul_actions = [
            'MVV_MODUL_NEW',
            'MVV_MODUL_UPDATE',
            'MVV_MODUL_DEL',
            'MVV_MODUL_DESK_NEW',
            'MVV_MODUL_DESK_UPDATE',
            'MVV_MODUL_DESK_DEL',
            'MVV_MODULINST_NEW',
            'MVV_MODULINST_DEL',
            'MVV_MODULINST_UPDATE',
            'MVV_MODUL_USER_NEW',
            'MVV_MODUL_USER_DEL',
            'MVV_MODUL_USER_UPDATE',
            'MVV_MODUL_LANG_NEW',
            'MVV_MODUL_LANG_DEL',
            'MVV_MODUL_LANG_UPDATE',
            'MVV_MODULTEIL_STGTEILABS_NEW',
            'MVV_MODULTEIL_STGTEILABS_DEL',
            'MVV_MODULTEIL_STGTEILABS_UPDATE',
            'MVV_STGTEILABS_MODUL_NEW',
            'MVV_STGTEILABS_MODUL_DEL',
            'MVV_STGTEILABS_MODUL_UPDATE'
        ];

        if (in_array($action_name, $modul_actions)) {
            $module = Modul::findBySQL("code LIKE CONCAT('%', " . $sql_needle . ", '%') OR modul_id = " . $sql_needle);
            $deskriptoren = ModulDeskriptor::findBySql("bezeichnung LIKE CONCAT('%', " . $sql_needle . ", '%') OR deskriptor_id = " . $sql_needle);
            foreach ($module as $modul) {
                $result[] = [
                    $modul->getId(),
                    $modul->getDisplayName()
                ];
            }
            foreach ($deskriptoren as $desk) {
                $modul = Modul::find($desk->modul_id);
                $result[] = [
                    $modul->getId(),
                    $modul->getDisplayName()
                ];
            }
        }

        $modulteile_actions = [
            'MVV_MODULTEIL_NEW',
            'MVV_MODULTEIL_UPDATE',
            'MVV_MODULTEIL_DEL',
            'MVV_MODULTEIL_DESK_NEW',
            'MVV_MODULTEIL_DESK_UPDATE',
            'MVV_MODULTEIL_DESK_DEL',
            'MVV_MODULTEIL_LANG_NEW',
            'MVV_MODULTEIL_LANG_DEL',
            'MVV_MODULTEIL_LANG_UPDATE',
            'MVV_LVMODULTEIL_NEW',
            'MVV_LVMODULTEIL_DEL',
            'MVV_LVMODULTEIL_UPDATE'
        ];

        if (in_array($action_name, $modulteile_actions)) {
            $deskriptoren = ModulDeskriptor::findBySql("bezeichnung LIKE CONCAT('%', " . $sql_needle . ", '%') OR deskriptor_id = " . $sql_needle);
            foreach ($deskriptoren as $desk) {
                $modulteil = Modulteil::find($desk->modulteil_id);
                $result[] = [
                    $modulteil->getId(),
                    $modulteil->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_STUDIENGANG_NEW',
            'MVV_STUDIENGANG_UPDATE',
            'MVV_STUDIENGANG_DEL',
            'MVV_STG_STGTEIL_NEW',
            'MVV_STG_STGTEIL_DEL',
            'MVV_STG_STGTEIL_UPDATE'
        ])) {
            $stg = Studiengang::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR studiengang_id = " . $sql_needle);
            foreach ($stg as $studiengang) {
                $result[] = [
                    $studiengang->getId(),
                    $studiengang->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_STGTEIL_NEW',
            'MVV_STGTEIL_UPDATE',
            'MVV_STGTEIL_DEL',
            'MVV_FACHBERATER_NEW',
            'MVV_FACHBERATER_UPDATE',
            'MVV_FACHBERATER_DEL'
        ])) {
            $stgteile = StudiengangTeil::findBySQL("zusatz LIKE CONCAT('%', " . $sql_needle . ", '%') OR zusatz_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR stgteil_id = " . $sql_needle);
            foreach ($stgteile as $stgteil) {
                $result[] = [
                    $stgteil->getId(),
                    $stgteil->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_STGTEILVERSION_NEW',
            'MVV_STGTEILVERSION_UPDATE',
            'MVV_STGTEILVERSION_DEL'
        ])) {
            $versionen = StgteilVersion::findBySQL("code LIKE CONCAT('%', " . $sql_needle . ", '%') OR version_id = " . $sql_needle . " OR stgteil_id = " . $sql_needle);
            foreach ($versionen as $version) {
                $result[] = [
                    $version->getId(),
                    $version->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_STGTEILBEZ_NEW',
            'MVV_STGTEILBEZ_UPDATE',
            'MVV_STGTEILBEZ_DEL'
        ])) {
            $stgbez = StgteilBezeichnung::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR stgteil_bez_id = " . $sql_needle);
            foreach ($stgbez as $bez) {
                $result[] = [
                    $bez->getId(),
                    $bez->getDisplayName()
                ];
            }
        }

        $stgteil_actions = [
            'MVV_STGTEILABS_NEW',
            'MVV_STGTEILABS_UPDATE',
            'MVV_STGTEILABS_DEL',
            'MVV_MODULTEIL_STGTEILABS_NEW',
            'MVV_MODULTEIL_STGTEILABS_DEL',
            'MVV_MODULTEIL_STGTEILABS_UPDATE',
            'MVV_STG_STGTEIL_NEW',
            'MVV_STG_STGTEIL_DEL',
            'MVV_STG_STGTEIL_UPDATE',
            'MVV_STGTEILABS_MODUL_NEW',
            'MVV_STGTEILABS_MODUL_DEL',
            'MVV_STGTEILABS_MODUL_UPDATE'
        ];

        if (in_array($action_name, $stgteil_actions)) {
            $stgteilabs = Lvgruppe::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR abschnitt_id = " . $sql_needle);
            foreach ($stgteilabs as $abschnitt) {
                $result[] = [
                    $abschnitt->getId(),
                    $abschnitt->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_LVGRUPPE_NEW',
            'MVV_LVGRUPPE_DEL',
            'MVV_LVGRUPPE_UPDATE',
            'MVV_LVMODULTEIL_NEW',
            'MVV_LVMODULTEIL_DEL',
            'MVV_LVMODULTEIL_UPDATE',
            'MVV_LVSEMINAR_NEW',
            'MVV_LVSEMINAR_DEL',
            'MVV_LVSEMINAR_UPDATE'
        ])) {
            $lvgruppen = Lvgruppe::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR lvgruppe_id = " . $sql_needle);
            foreach ($lvgruppen as $lvgruppe) {
                $result[] = [
                    $lvgruppe->getId(),
                    $lvgruppe->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_FACH_NEW',
            'MVV_FACH_UPDATE',
            'MVV_FACH_DEL',
            'MVV_FACHINST_NEW',
            'MVV_FACHINST_DEL',
            'MVV_FACHINST_UPDATE'
        ])) {
            $faecher = Fach::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR fach_id = " . $sql_needle);
            foreach ($faecher as $fach) {
                $result[] = [
                    $fach->getId(),
                    $fach->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_ABSCHLUSS_NEW',
            'MVV_ABSCHLUSS_UPDATE',
            'MVV_ABSCHLUSS_DEL',
            'MVV_ABS_ZUORD_NEW',
            'MVV_ABS_ZUORD_DEL',
            'MVV_ABS_ZUORD_UPDATE'
        ])) {
            $abschluesse = Abschluss::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR abschluss_id = " . $sql_needle);
            foreach ($abschluesse as $abschluss) {
                $result[] = [
                    $abschluss->getId(),
                    $abschluss->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_KATEGORIE_NEW',
            'MVV_KATEGORIE_UPDATE',
            'MVV_KATEGORIE_DEL',
            'MVV_ABS_ZUORD_NEW',
            'MVV_ABS_ZUORD_DEL',
            'MVV_ABS_ZUORD_UPDATE'
        ])) {
            $abskategorien = AbschlussKategorie::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR kategorie_id = " . $sql_needle);
            foreach ($abskategorien as $abskategorie) {
                $result[] = [
                    $abskategorie->getId(),
                    $abskategorie->getDisplayName()
                ];
            }
        }

        if (in_array($action_name, [
            'MVV_DOKUMENT_NEW',
            'MVV_DOKUMENT_UPDATE',
            'MVV_DOKUMENT_DEL',
            'MVV_DOK_ZUORD_NEW',
            'MVV_DOK_ZUORD_DEL',
            'MVV_DOK_ZUORD_UPDATE'
        ])) {
            $dokumente = MvvDokument::findBySQL("name LIKE CONCAT('%', " . $sql_needle . ", '%') OR name_en LIKE CONCAT('%', " . $sql_needle . ", '%') OR dokument_id = " . $sql_needle);
            foreach ($dokumente as $dokument) {
                $result[] = [
                    $dokument->getId(),
                    $dokument->getDisplayName()
                ];
            }
        }

        return $result;
    }

}
