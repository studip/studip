<?php

$GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'] = array(
    'akkreditierung'    => array('visible' => 1, 'name' => _('Akkreditierungsfassung')),
    'aenderung'         => array('visible' => 1, 'name' => _('Änderungsfassung')),
    'reform'            => array('visible' => 1, 'name' => _('Reformfassung')),
    'deregulierung'     => array('visible' => 1, 'name' => _('Deregulierungsfassung'))
);

$GLOBALS['MVV_NAME_SEMESTER'] = array(
    'values'            => array(
        'ss'                => array('visible' => 1, 'name' => _('Sommersemester')),
        'ws'                => array('visible' => 1, 'name' => _('Wintersemester')),
        'ss_ws'             => array('visible' => 1, 'name' => _('Sommersemester und Wintersemester'))
    ),
    'default'           => 'ws'
);

// Module
$GLOBALS['MVV_MODUL']['KP_KOEFFIZIENT'] = 30;

$GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN'] = array(
    'values'            => array(
        'verantwortung'     => array('visible' => 1, 'name' => _('Modulverantwortung')),
        'pruefung'          => array('visible' => 1, 'name' => _('Prüfung')),
        'beratung'          => array('visible' => 1, 'name' => _('Modulberatung'))
    )
);

$GLOBALS['MVV_MODUL']['TERMIN_TYPEN'] = array(
    'values'            => array(
        'pruefung'          => array('visible' => 1, 'name' => _('Prüfung')),
        'nach'              => array('visible' => 1, 'name' => _('Nachprüfung')),
        'ausgleich'         => array('visible' => 1, 'name' => _('Ausgleichsprüfung')),
        'wiederholung'      => array('visible' => 1, 'name' => _('Wiederholungsprüfung')),
        'einschreibung'     => array('visible' => 1, 'name' => _('Einschreibung'))
    ),
    'default'           => ''
);

$GLOBALS['MVV_MODUL']['SPRACHE'] = array(
    'values'            => array(
        'DE'                => array('visible' => 1, 'name' => _('Deutsch')),
        'EN'                => array('visible' => 1, 'name' => _('Englisch')),
        'AR'                => array('visible' => 1, 'name' => _('Arabisch')),
        'BS'                => array('visible' => 1, 'name' => _('Bosnisch/Kroatisch/Serbisch')),
        'ZH'                => array('visible' => 1, 'name' => _('Chinesisch')),
        'FR'                => array('visible' => 1, 'name' => _('Französisch')),
        'GRC'               => array('visible' => 1, 'name' => _('Griechisch')),
        'HE'                => array('visible' => 1, 'name' => _('Hebräisch')),
        'IT'                => array('visible' => 1, 'name' => _('Italienisch')),
        'JA'                => array('visible' => 1, 'name' => _('Japanisch')),
        'LA'                => array('visible' => 1, 'name' => _('Latein')),
        'EL'                => array('visible' => 1, 'name' => _('Neugriechisch')),
        'NI'                => array('visible' => 1, 'name' => _('Niederländisch')),
        'NO'                => array('visible' => 1, 'name' => _('Norwegisch')),
        'PL'                => array('visible' => 1, 'name' => _('Polnisch')),
        'PT'                => array('visible' => 1, 'name' => _('Portugiesisch')),
        'RU'                => array('visible' => 1, 'name' => _('Russisch')),
        'SV'                => array('visible' => 1, 'name' => _('Schwedisch')),
        'ES'                => array('visible' => 1, 'name' => _('Spanisch')),
        'CS'                => array('visible' => 1, 'name' => _('Tschechisch')),
        'TR'                => array('visible' => 1, 'name' => _('Türkisch')),
        'UK'                => array('visible' => 1, 'name' => _('Ukrainisch')),
        'HU'                => array('visible' => 1, 'name' => _('Ungarisch'))
    ),
    'default'           => 'DE'
);

$GLOBALS['MVV_MODULTEIL']['SPRACHE'] = $GLOBALS['MVV_MODUL']['SPRACHE'];

$GLOBALS['MVV_MODUL']['PRUEF_EBENE'] = array(
    'values'            => array(
        'abschliessend'     => array('visible' => 1, 'name' => _('modulabschließend')),
        'begleitend'        => array('visible' => 1, 'name' => _('modulbegleitend'))
    ),
    'default'           => ''
);
// Status der Bearbeitung
$GLOBALS['MVV_MODUL']['STATUS'] = array(
    'values'            => array(
        // don't remove the status planung! It is internally used.
        'planung' => array(
            'visible' => 1,
            'name' => _('Entwurf'),
            'public' => 0,
            'icon' => Icon::create('span-2quarter', 'status-yellow')),
        'genehmigt' => array(
            'visible' => 1,
            'name' => _('genehmigt und aktiv'),
            'public' => 1,
            'icon' => Icon::create('span-full', 'status-green')),
        'ausgelaufen' => array(
            'visible' => 1,
            'name' => _('ausgelaufen und nicht mehr aktiv'),
            'public' => 1,
            'icon' => Icon::create('span-empty', 'status-red'))
    ),
    'default'           => 'planung'
);

$GLOBALS['MVV_MODUL']['FAKTOR_NOTE']['default'] = '1';

$GLOBALS['MVV_MODUL']['FASSUNG_TYP'] = $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'];

$GLOBALS['MVV_STUDIENGANG']['STATUS'] = $GLOBALS['MVV_MODUL']['STATUS'];

$GLOBALS['MVV_MODUL']['INSTITUT_GRUPPEN'] = array(
    'values'            => array(
        'hauptverantwortlich' => array('visible' => 1, 'name' => _('Hauptverantwortliche (geschäftsführende) Einrichtung')),
        'verantwortlich'      => array('visible' => 1, 'name' => _('Verantwortliche Einrichtung'))
    )
);

// Moduldeskriptor Ausgabesprache
$GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE'] = array(
    'values'            => array(
        'DE'                => array('visible' => 1, 'name' => _('Originalfassung'), 'content_language' => 'de_DE'),
        'EN'                => array('visible' => 1, 'name' => _('Englisch'), 'content_language' => 'en_GB')
    //    'de_DE'                => array('visible' => 1, 'name' => _('Originalfassung')),
    //    'en_GB'                => array('visible' => 1, 'name' => _('Englisch'))
    ),
    'default'           => 'DE'
);

// Modulteile
$GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG'] = array(
    'values'            => array(
        'lv'                => array('visible' => 1, 'name' => _('LV')),
    ),
    'default'           => 'lv'
);

$GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM'] = array(
    'values'                => array(
        'g_vorlesung'           => array('visible' => 1, 'name' => _('Vorlesung'), 'parent' => ''),
            'vorlesung'             => array('visible' => 1, 'name' => _('Vorlesung'), 'parent' => 'g_vorlesung'),
            'basisvorlesung'        => array('visible' => 1, 'name' => _('Basisvorlesung'), 'parent' => 'g_vorlesung'),
            'ringvorlesung'         => array('visible' => 1, 'name' => _('Ringvorlesung'), 'parent' => 'g_vorlesung'),
        'g_seminar'             => array('visible' => 1, 'name' => _('Seminar'), 'parent' => ''),
            'seminar'               => array('visible' => 1, 'name' => _('Seminar'), 'parent' => 'g_seminar'),
            'einfuehrungsseminar'   => array('visible' => 1, 'name' => _('Einführungsseminar'), 'parent' => 'g_seminar'),
            'proseminar'            => array('visible' => 1, 'name' => _('Proseminar'), 'parent' => 'g_seminar'),
            'hauptseminar'          => array('visible' => 1, 'name' => _('Hauptseminar'), 'parent' => 'g_seminar'),
            'obersemminar'          => array('visible' => 1, 'name' => _('Oberseminar'), 'parent' => 'g_seminar'),
        'g_uebung'              => array('visible' => 1, 'name' => _('Übung/Tutorium/Sprache'), 'parent' => ''),
            'uebung'                => array('visible' => 1, 'name' => _('Übung'), 'parent' => 'g_uebung'),
            'tutorium'              => array('visible' => 1, 'name' => _('Tutorium'), 'parent' => 'g_uebung'),
            'sprachkurs'            => array('visible' => 1, 'name' => _('Sprachkurs'), 'parent' => 'g_uebung'),
            'sprachuebung'          => array('visible' => 1, 'name' => _('Sprachübung'), 'parent' => 'g_uebung'),
            'lektuereuebung'        => array('visible' => 1, 'name' => _('Lektüreübung'), 'parent' => 'g_uebung'),
            'uebersetzungsuebung'   => array('visible' => 1, 'name' => _('Übersetzungsübung'), 'parent' => 'g_uebung'),
            'stiluebung'            => array('visible' => 1, 'name' => _('Stilübung'), 'parent' => 'g_uebung'),
        'g_kolloquium'          => array('visible' => 1, 'name' => _('Kolloquium'), 'parent' => ''),
            'kolloquium'            => array('visible' => 1, 'name' => _('Kolloquium'), 'parent' => 'g_kolloquium'),
            'forschungskolloquium'  => array('visible' => 1, 'name' => _('Forschungskolloquium'), 'parent' => 'g_kolloquium'),
            'examenskolloquium'     => array('visible' => 1, 'name' => _('Examenskolloquium'), 'parent' => 'g_kolloquium'),
        'g_praxis'              => array('visible' => 1, 'name' => _('Praxis/Exkursion'), 'parent' => ''),
            'exkursion'             => array('visible' => 1, 'name' => _('Exkursion'), 'parent' => 'g_praxis'),
            'praktischerkurs'       => array('visible' => 1, 'name' => _('Praktischer Kurs'), 'parent' => 'g_praxis'),
            'sportpraktischerkurs'  => array('visible' => 1, 'name' => _('Sportpraktischer Kurs'), 'parent' => 'g_praxis'),
            'kuenstlerischerunterricht' => array('visible' => 1, 'name' => _('Künstlerischer Unterricht'), 'parent' => 'g_praxis'),
            'praktikum'             => array('visible' => 1, 'name' => _('Praktikum'), 'parent' => 'g_praxis'),
        'g_projekt'             => array('visible' => 1, 'name' => _('Projekt'), 'parent' => ''),
            'projekt'               => array('visible' => 1, 'name' => _('Projekt'), 'parent' => 'g_projekt'),
            'projektseminar'        => array('visible' => 1, 'name' => _('Projektseminar'), 'parent' => 'g_projekt'),
            'projektpraktikum'      => array('visible' => 1, 'name' => _('Projektpraktikum'), 'parent' => 'g_projekt'),
            'szenischesprojekt'     => array('visible' => 1, 'name' => _('Szenisches Projekt'), 'parent' => 'g_projekt'),
        'g_sonstiges'           => array('visible' => 1, 'name' => _('Sonstiges'), 'parent' => ''),
            'freielektuere'         => array('visible' => 1, 'name' => _('Freie Lektüre'), 'parent' => 'g_sonstiges'),
            'grundkurs'             => array('visible' => 1, 'name' => _('Grundkurs'), 'parent' => 'g_sonstiges'),
            'klausur'               => array('visible' => 1, 'name' => _('Klausur'), 'parent' => 'g_sonstiges'),
            'kursus'                => array('visible' => 1, 'name' => _('Kursus'), 'parent' => 'g_sonstiges'),
            'modul'                 => array('visible' => 1, 'name' => _('Modul'), 'parent' => 'g_sonstiges'),
            'repetitorium'          => array('visible' => 1, 'name' => _('Repetitorium'), 'parent' => 'g_sonstiges'),
            'selbstaendigebetreutearbeit' => array('visible' => 1, 'name' => _('Selbständige betreute Arbeit'), 'parent' => 'g_sonstiges'),
            'workshop'              => array('visible' => 1, 'name' => _('Workshop'), 'parent' => 'g_sonstiges'),
        'g_kombinationen'       => array('visible' => 1, 'name' => _('Kombinationen'), 'parent' => ''),
            'vorlesungseminar'      => array('visible' => 1, 'name' => _('Vorlesung oder Seminar'), 'parent' => 'g_kombinationen'),
            'vorlesungproseminar'   => array('visible' => 1, 'name' => _('Vorlesung oder Proseminar'), 'parent' => 'g_kombinationen'),
            'vorlesunghauptseminar' => array('visible' => 1, 'name' => _('Vorlesung oder Hauptseminar'), 'parent' => 'g_kombinationen'),
            'vorlesunguebung'       => array('visible' => 1, 'name' => _('Vorlesung oder Übung'), 'parent' => 'g_kombinationen'),
            'vorlsemlektuere'       => array('visible' => 1, 'name' => _('Vorlesung, Seminar oder Lektüreübung'), 'parent' => 'g_kombinationen'),
            'vorlsemeinf'           => array('visible' => 1, 'name' => _('Vorlesung, Seminar oder Einführungsveranstaltungen'), 'parent' => 'g_kombinationen'),
            'vorlsemeinflektuere'   => array('visible' => 1, 'name' => _('Vorlesung, Seminar, Einführung oder Lektüreübung'), 'parent' => 'g_kombinationen'),
            'seminaroberseminar'    => array('visible' => 1, 'name' => _('Seminar oder Oberseminar'), 'parent' => 'g_kombinationen'),
            'seminarprojektseminar' => array('visible' => 1, 'name' => _('Seminar oder Projektseminar'), 'parent' => 'g_kombinationen'),
            'seminaruebung'         => array('visible' => 1, 'name' => _('Seminar oder Übung'), 'parent' => 'g_kombinationen'),
            'seminarlektuere'       => array('visible' => 1, 'name' => _('Seminar oder Lektürekurs'), 'parent' => 'g_kombinationen'),
            'seminarszenischesprojekt' => array('visible' => 1, 'name' => _('Seminar oder Szenisches Projekt'), 'parent' => 'g_kombinationen'),
            'seminaruebungexkursion' => array('visible' => 1, 'name' => _('Seminar, Übung oder Exkursion'), 'parent' => 'g_kombinationen'),
            'semszenprojektkuenstleistung' => array('visible' => 1, 'name' => _('Seminar, Szenisches Projekt oder eigene Künstlerische Leistung'), 'parent' => 'g_kombinationen'),
            'uebungprojekt'        => array('visible' => 1, 'name' => _('Übung oder Projekt'), 'parent' => 'g_kombinationen'),
            'uebungtutorium'       => array('visible' => 1, 'name' => _('Übung oder Tutorium'), 'parent' => 'g_kombinationen'),
            'uebunglektuerekurs'   => array('visible' => 1, 'name' => _('Übung oder Lektürekurs'), 'parent' => 'g_kombinationen'),
            'szenprojektkuenstleistung' => array('visible' => 1, 'name' => _('Szenisches Projekt oder eigene künstlerische Leistung'), 'parent' => 'g_kombinationen'),
            'praktkursfestivalorga' => array('visible' => 1, 'name' => _('Praktischer Kurs oder Festivalorganisation'), 'parent' => 'g_kombinationen'),
    ),
    'default'           => ''
);

// Modulteildeskriptor
$GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE'] = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE'];

// Maximale Anzahl Fachsemester
$GLOBALS['MVV_MODULTEIL_FACHSEMESTER'] = 10;

// Status des zugeordneten Fachsemesters
$GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS'] = array(
    'values' => array(
        'kann' => array(
            'visible' => 1,
            'name'    => _('kann'),
            'icon'    => 'o'),
        'soll' => array(
            'visible' => 1,
            'name'    => _('soll'),
            'icon'    => '+'),
        'muss' => array(
            'visible' => 1,
            'name'    => _('muss'),
            'icon'    => '#')
    ),
    'default' => ''
);

$GLOBALS['MVV_STGTEILVERSION']['STATUS'] = $GLOBALS['MVV_MODUL']['STATUS'];

$GLOBALS['MVV_STGTEIL']['STATUS'] = $GLOBALS['MVV_MODUL']['STATUS'];

$GLOBALS['MVV_LANGUAGES'] = array(
    'values'            => array(
        'DE'                => array('visible' => 1, 'name' => _('Deutsch'),
                                'locale' => 'de_DE'),
        'EN'                => array('visible' => 1, 'name' => _('Englisch'),
                                'locale' => 'en_GB')
    ),
    'default'           => 'DE'
);

$GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'] = $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'];