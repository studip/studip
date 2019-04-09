<?php
/**
 * LockRules.class.php
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Mark Sievers <msievers@uos.de>
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2011 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

/**
* LockRules.class.php
*
* This class contains only static methods dealing with lock rules
*
*/

class LockRules {

    private static $lockmap = [];
    private static $lockrules = [];

    /**
     * get lockrule object for given id
     * from static object pool
     *
     * @param string $lock_id id of lockrule
     * @return LockRule
     */
    public static function get($lock_id)
    {
        if(!array_key_exists($lock_id, self::$lockrules)) {
            self::$lockrules[$lock_id] = LockRule::find($lock_id);
        }
        return self::$lockrules[$lock_id];
    }

    /**
     * returns a list of lockrules that can be administrated
     * with the given user id
     *
     * @param string $user_id id of user
     * @return array of LockRule objects
     */
    public static function getAdministrableSeminarRules($user_id)
    {
        return array_filter(LockRule::findAllByType('sem'), function ($rule) use ($user_id) {
            return $GLOBALS['perm']->get_perm($user_id) === 'root'
                || (
                    $rule->user_id === $user_id
                    && !in_array($rule->permission, ['root', 'admin'])
                );
        });
    }

    /**
     * returns a list of lockrules that can be applied to a course
     * with the given user id
     *
     * @param string $user_id id of user
     * @return array of LockRule objects
     */
    public static function getAvailableSeminarRules($user_id)
    {
        return array_filter(LockRule::findAllByType('sem'), function ($rule) use ($user_id) {
            return $GLOBALS['perm']->get_perm($user_id) === 'root'
                || !in_array($rule->permission, ['root', 'admin']);
        });
    }

    /**
     * returns the lock rule object for the given id, else null
     *
     * @param string $object_id id of course, institute or user
     * @param bool $renew if true, reloads the rule from database
     * @param string|null $object_type : The type of object you want to check: "user", "sem" or "inst"
     * @return LockRule
     */
    public static function getObjectRule($object_id, $renew = false, $object_type = null)
    {
        if(!array_key_exists($object_id, self::$lockmap) || $renew) {
            if ($object_type === null) {
                $object_type = get_object_type($object_id, words('sem inst user'));
            }
            if ($object_type) {
                $methodmap = ['sem'  => 'Seminar',
                                   'inst' => 'Institute',
                                   'fak'  => 'Institute',
                                   'user' => 'User'];
                $lr = call_user_func(['LockRule', 'FindBy' . $methodmap[$object_type]], $object_id);
                if ($lr) {
                    self::$lockmap[$object_id] = $lr->getId();
                    self::$lockrules[$lr->getId()] = $lr;
                } else {
                    self::$lockmap[$object_id] = null;
                }
            }
        }
        return self::$lockmap[$object_id] ? self::$lockrules[self::$lockmap[$object_id]] : null;
    }

    /**
     * checks if an attribute of an entity is locked for the current user
     * see self::getLockRuleConfig() for the list of attributes
     *
     * @param string $object_id id of course, institute or user
     * @param string $attribute the name of an lockable attribute
     * @param string|null $object_type : The type of object you want to check: "user", "sem" or "inst"
     * @return boolean true if attribute is locked for the current user
     */
    public static function Check($object_id, $attribute, $object_type = null)
    {
        $lr = self::getObjectRule($object_id, false, $object_type);
        if ($lr) {
            return $lr['attributes'][mb_strtolower($attribute)] == 1 && self::CheckLockRulePermission($object_id);
        } else {
            return false;
        }
    }

    /**
     * checks if given entity is locked for the current user
     *
     * @param string $object_id id of course, institute or user
     * @return boolean true if given entity is locked fpr the current user
     */
    public static function CheckLockRulePermission($object_id)
    {
        $perms = ['autor','tutor','dozent','admin','root','god'];
        $lr = self::getObjectRule($object_id);

        if ($lr) {
            $pk = array_search($lr->permission, $perms);
            $check_perm = $perms[$pk + 1];
            if ($lr->object_type == 'sem') {
                return ($lr->permission == 'root' || !$GLOBALS['perm']->have_studip_perm($check_perm, $object_id));
    }
            if ($lr->object_type == 'inst') {
                return ($lr->permission == 'root' || !$GLOBALS['perm']->have_perm('root'));
            }
            if ($lr->object_type == 'user') {
                return ($lr->permission == 'root' || !$GLOBALS['perm']->have_perm($check_perm));
            }
        }
        return false;
    }

    /**
     * returns an array containing all lockable attributes for
     * given entity type
     *
     * @param string $type entity type, one of [sem,inst,user]
     * @return array
     */
    public static function getLockRuleConfig($type)
    {
        $groups['basic'] = _("Grunddaten");
        $groups['personnel'] = _("Personen und Einordnung");
        $groups['misc'] = _("weitere Daten");
        $groups['room_time'] = _("Zeiten/Räume");
        $groups['access'] = _("Zugangsberechtigungen");
        $groups['actions'] = _("spezielle Aktionen");

        $attributes['sem']['veranstaltungsnummer'] = ['name' => _("Veranstaltungsnummer"), 'group' => 'basic'];
        $attributes['sem']['seminar_inst'] = ['name' => _("beteiligte Einrichtungen"), 'group' => 'basic'];
        $attributes['sem']['name'] = ['name' => _("Name"), 'group' => 'basic'];
        $attributes['sem']['untertitel'] = ['name' => _("Untertitel"), 'group' => 'basic'];
        $attributes['sem']['status'] = ['name' => _("Status"), 'group' => 'basic'];
        $attributes['sem']['beschreibung'] = ['name' => _("Beschreibung"), 'group' => 'basic'];
        $attributes['sem']['ort'] = ['name' => _("Ort"), 'group' => 'basic'];
        $attributes['sem']['art'] = ['name' => _("Veranstaltungstyp"), 'group' => 'basic'];
        $attributes['sem']['ects'] = ['name' => _("ECTS-Punkte"), 'group' => 'basic'];
        $attributes['sem']['admission_turnout'] = ['name' => _("Teilnehmendenzahl"), 'group' => 'basic'];
        $attributes['sem']['dozent'] = ['name' => _("Lehrende"), 'group' => 'personnel'];
        $attributes['sem']['tutor'] = ['name' => _("Tutor/-innen"), 'group' => 'personnel'];
        $attributes['sem']['institut_id'] = ['name' => _("Heimateinrichtung"), 'group' => 'personnel'];
        $attributes['sem']['sem_tree'] = ['name' => _("Studienbereiche"), 'group' => 'personnel'];
        $attributes['sem']['mvv_lvgruppe'] = ['name' => _("Modulzuordnung"), 'group' => 'personnel'];
        $attributes['sem']['participants'] = ['name' => _("Personen hinzufügen/löschen"), 'group' => 'personnel'];
        $attributes['sem']['groups'] = ['name' => _("Gruppen hinzufügen/löschen"), 'group' => 'personnel'];
        $attributes['sem']['sonstiges'] = ['name' => _("Sonstiges"), 'group' => 'misc'];
        $attributes['sem']['teilnehmer'] = ['name' => _("Beschreibung des Teilnehmendenkreises"), 'group' => 'misc'];
        $attributes['sem']['voraussetzungen'] = ['name' => _("Teilnahmevoraussetzungen"), 'group' => 'misc'];
        $attributes['sem']['lernorga'] = ['name' => _("Lernorganisation"), 'group' => 'misc'];
        $attributes['sem']['leistungsnachweis'] = ['name' => _("Leistungsnachweis"), 'group' => 'misc'];
        $attributes['sem']['room_time'] = ['name' => _("Zeiten/Räume"), 'group' => 'room_time'];
        $attributes['sem']['cancelled_dates'] = ['name' => _("Termine ausfallen lassen"), 'group' => 'room_time'];
        $attributes['sem']['edit_dates_in_schedule'] = ['name' => _("Erweiterte Termindaten im Ablaufplan ändern"), 'group' => 'room_time'];
        $attributes['sem']['admission_endtime'] = ['name' => _("Zeit/Datum der Platzverteilung/Kontingentierung"), 'group' => 'access'];
        $attributes['sem']['admission_disable_waitlist'] = ['name' => _("Aktivieren/Deaktivieren der Warteliste"), 'group' => 'access'];
        $attributes['sem']['admission_binding'] = ['name' => _("Verbindlichkeit der Anmeldung"), 'group' => 'access'];
        $attributes['sem']['admission_type'] = ['name' => _("Typ des Anmeldeverfahrens"), 'group' => 'access'];
        $attributes['sem']['admission_prelim'] = ['name' => _("zugelassenene Studiengänge"), 'group' => 'access'];
        $attributes['sem']['admission_prelim_txt'] = ['name' => _("Vorläufigkeit der Anmeldungen"), 'group' => 'access'];
        $attributes['sem']['admission_disable_waitlist'] = ['name' => _("Hinweistext bei Anmeldungen"), 'group' => 'access'];
        $attributes['sem']['admission_starttime'] = ['name' => _("Startzeitpunkt der Anmeldemöglichkeit"), 'group' => 'access'];
        $attributes['sem']['admission_endtime_sem'] = ['name' => _("Endzeitpunkt der Anmeldemöglichkeit"), 'group' => 'access'];
        $attributes['sem']['lesezugriff'] = ['name' => _("Lesezugriff"), 'group' => 'access'];
        $attributes['sem']['schreibzugriff'] = ['name' => _("Schreibzugriff"), 'group' => 'access'];
        $attributes['sem']['passwort'] = ['name' => _("Passwort"), 'group' => 'access'];
        $attributes['sem']['user_domain'] = ['name' => _("Nutzerdomänen zuordnen"), 'group' => 'access'];
        $attributes['sem']['seminar_copy'] = ['name' => _("Veranstaltung kopieren"), 'group' => 'actions'];
        $attributes['sem']['seminar_archive'] = ['name' => _("Veranstaltung archivieren"), 'group' => 'actions'];
        $attributes['sem']['seminar_visibility'] = ['name' => _("Veranstaltung sichtbar/unsichtbar schalten"), 'group' => 'actions'];

        $attributes['inst']['name'] = ['name' => _("Name"), 'group' => 'basic'];
        $attributes['inst']['fakultaets_id'] = ['name' => _("Fakultät"), 'group' => 'basic'];
        $attributes['inst']['type'] = ['name' => _("Bezeichnung"), 'group' => 'basic'];
        $attributes['inst']['strasse'] = ['name' => _("Straße"), 'group' => 'basic'];
        $attributes['inst']['plz'] = ['name' => _("Ort"), 'group' => 'basic'];
        $attributes['inst']['telefon'] = ['name' => _("Telefonnummer"), 'group' => 'basic'];
        $attributes['inst']['fax'] = ['name' => _("Faxnummer"), 'group' => 'basic'];
        $attributes['inst']['email'] = ['name' => _("E-Mail-Adresse"), 'group' => 'basic'];
        $attributes['inst']['url'] = ['name' => _("Homepage"), 'group' => 'basic'];
        $attributes['inst']['participants'] = ['name' => _("Mitarbeiter hinzufügen/löschen"), 'group' => 'personnel'];
        $attributes['inst']['groups'] = ['name' => _("Gruppen hinzufügen/löschen"), 'group' => 'personnel'];

        $attributes['user']['name'] = ['name' => _("Vor- und Nachname"), 'group' => 'basic'];
        $attributes['user']['username'] = ['name' => _("Nutzername"), 'group' => 'basic'];
        $attributes['user']['password'] = ['name' => _("Passwort"), 'group' => 'basic'];
        $attributes['user']['email'] = ['name' => _("E-Mail"), 'group' => 'basic'];
        $attributes['user']['title'] = ['name' => _("Titel"), 'group' => 'basic'];
        $attributes['user']['gender'] = ['name' => _("Geschlecht"), 'group' => 'basic'];
        $attributes['user']['privatnr'] = ['name' => _("Telefon (privat)"), 'group' => 'basic'];
        $attributes['user']['privatcell'] = ['name' => _("Mobiltelefon"), 'group' => 'basic'];
        $attributes['user']['privadr'] = ['name' => _("Adresse (privat)"), 'group' => 'basic'];
        $attributes['user']['hobby'] = ['name' => _("Hobbys"), 'group' => 'basic'];
        $attributes['user']['lebenslauf'] = ['name' => _("Lebenslauf"), 'group' => 'basic'];
        $attributes['user']['home'] = ['name' => _("Homepage"), 'group' => 'basic'];
        $attributes['user']['publi'] = ['name' => _("Schwerpunkte"), 'group' => 'misc'];
        $attributes['user']['schwerp'] = ['name' => _("Publikationen"), 'group' => 'misc'];
        $attributes['user']['institute_data'] = ['name' => _("Einrichtungsdaten"), 'group' => 'misc'];

        foreach(DataField::getDataFields($type) as $df) {
            $attributes[$type][$df->datafield_id] = ['name' => $df->name, 'group' => 'misc'];
        }

        return ['groups' => $groups,'attributes' => $attributes[$type]];
    }

}
