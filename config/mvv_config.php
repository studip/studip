<?php

$GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'] = [
    'akkreditierung'    => ['visible' => 1, 'name' => _('Akkreditierungsfassung')],
    'aenderung'         => ['visible' => 1, 'name' => _('Änderungsfassung')],
    'reform'            => ['visible' => 1, 'name' => _('Reformfassung')],
    'deregulierung'     => ['visible' => 1, 'name' => _('Deregulierungsfassung')]
];

$GLOBALS['MVV_NAME_SEMESTER'] = [
    'values'            => [
        'ss'                => ['visible' => 1, 'name' => _('Sommersemester')],
        'ws'                => ['visible' => 1, 'name' => _('Wintersemester')],
        'ss_ws'             => ['visible' => 1, 'name' => _('Sommersemester und Wintersemester')]
    ],
    'default'           => 'ws'
];

// Module
$GLOBALS['MVV_MODUL']['KP_KOEFFIZIENT'] = 30;

$GLOBALS['MVV_MODUL']['PERSONEN_GRUPPEN'] = [
    'values'            => [
        'verantwortung'     => ['visible' => 1, 'name' => _('Modulverantwortung')],
        'pruefung'          => ['visible' => 1, 'name' => _('Prüfung')],
        'beratung'          => ['visible' => 1, 'name' => _('Modulberatung')]
    ]
];

$GLOBALS['MVV_MODUL']['TERMIN_TYPEN'] = [
    'values'            => [
        'pruefung'          => ['visible' => 1, 'name' => _('Prüfung')],
        'nach'              => ['visible' => 1, 'name' => _('Nachprüfung')],
        'ausgleich'         => ['visible' => 1, 'name' => _('Ausgleichsprüfung')],
        'wiederholung'      => ['visible' => 1, 'name' => _('Wiederholungsprüfung')],
        'einschreibung'     => ['visible' => 1, 'name' => _('Einschreibung')]
    ],
    'default'           => ''
];

$GLOBALS['MVV_MODUL']['SPRACHE'] = [
    'values'            => [
        'DE'                => ['visible' => 1, 'name' => _('Deutsch')],
        'EN'                => ['visible' => 1, 'name' => _('Englisch')],
        'AR'                => ['visible' => 1, 'name' => _('Arabisch')],
        'BS'                => ['visible' => 1, 'name' => _('Bosnisch/Kroatisch/Serbisch')],
        'ZH'                => ['visible' => 1, 'name' => _('Chinesisch')],
        'FR'                => ['visible' => 1, 'name' => _('Französisch')],
        'GRC'               => ['visible' => 1, 'name' => _('Griechisch')],
        'HE'                => ['visible' => 1, 'name' => _('Hebräisch')],
        'IT'                => ['visible' => 1, 'name' => _('Italienisch')],
        'JA'                => ['visible' => 1, 'name' => _('Japanisch')],
        'LA'                => ['visible' => 1, 'name' => _('Latein')],
        'EL'                => ['visible' => 1, 'name' => _('Neugriechisch')],
        'NI'                => ['visible' => 1, 'name' => _('Niederländisch')],
        'NO'                => ['visible' => 1, 'name' => _('Norwegisch')],
        'PL'                => ['visible' => 1, 'name' => _('Polnisch')],
        'PT'                => ['visible' => 1, 'name' => _('Portugiesisch')],
        'RU'                => ['visible' => 1, 'name' => _('Russisch')],
        'SV'                => ['visible' => 1, 'name' => _('Schwedisch')],
        'ES'                => ['visible' => 1, 'name' => _('Spanisch')],
        'CS'                => ['visible' => 1, 'name' => _('Tschechisch')],
        'TR'                => ['visible' => 1, 'name' => _('Türkisch')],
        'UK'                => ['visible' => 1, 'name' => _('Ukrainisch')],
        'HU'                => ['visible' => 1, 'name' => _('Ungarisch')]
    ],
    'default'           => 'DE'
];

$GLOBALS['MVV_MODULTEIL']['SPRACHE'] = $GLOBALS['MVV_MODUL']['SPRACHE'];

$GLOBALS['MVV_MODUL']['PRUEF_EBENE'] = [
    'values'            => [
        'abschliessend'     => ['visible' => 1, 'name' => _('modulabschließend')],
        'begleitend'        => ['visible' => 1, 'name' => _('modulbegleitend')]
    ],
    'default'           => ''
];
// Status der Bearbeitung
$GLOBALS['MVV_MODUL']['STATUS'] = [
    'values'            => [
        // don't remove the status planung! It is internally used.
        'planung' => [
            'visible' => 1,
            'name' => _('Entwurf'),
            'public' => 0,
            'icon' => Icon::create('span-2quarter', 'status-yellow')],
        'genehmigt' => [
            'visible' => 1,
            'name' => _('genehmigt und aktiv'),
            'public' => 1,
            'icon' => Icon::create('span-full', 'status-green')],
        'ausgelaufen' => [
            'visible' => 1,
            'name' => _('ausgelaufen und nicht mehr aktiv'),
            'public' => 1,
            'icon' => Icon::create('span-empty', 'status-red')]
    ],
    'default'           => 'planung'
];

$GLOBALS['MVV_MODUL']['FAKTOR_NOTE']['default'] = '1';

$GLOBALS['MVV_MODUL']['FASSUNG_TYP'] = $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'];

$GLOBALS['MVV_STUDIENGANG']['STATUS'] = $GLOBALS['MVV_MODUL']['STATUS'];

$GLOBALS['MVV_MODUL']['INSTITUT_GRUPPEN'] = [
    'values'            => [
        'hauptverantwortlich' => ['visible' => 1, 'name' => _('Hauptverantwortliche (geschäftsführende) Einrichtung')],
        'verantwortlich'      => ['visible' => 1, 'name' => _('Verantwortliche Einrichtung')]
    ]
];

// Moduldeskriptor Ausgabesprache
$GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE'] = [
    'values'            => [
        'DE'                => ['visible' => 1, 'name' => _('Originalfassung'), 'content_language' => 'de_DE'],
        'EN'                => ['visible' => 1, 'name' => _('Englisch'), 'content_language' => 'en_GB']
    //    'de_DE'                => array('visible' => 1, 'name' => _('Originalfassung')),
    //    'en_GB'                => array('visible' => 1, 'name' => _('Englisch'))
    ],
    'default'           => 'DE'
];

// Modulteile
$GLOBALS['MVV_MODULTEIL']['NUM_BEZEICHNUNG'] = [
    'values'            => [
        'lv'                => ['visible' => 1, 'name' => _('LV')],
    ],
    'default'           => 'lv'
];

$GLOBALS['MVV_MODULTEIL']['LERNLEHRFORM'] = [
    'values'                => [
        'g_vorlesung'           => ['visible' => 1, 'name' => 'Vorlesung', 'parent' => ''],
            'vorlesung'             => ['visible' => 1, 'name' => 'Vorlesung', 'parent' => 'g_vorlesung'],
            'basisvorlesung'        => ['visible' => 1, 'name' => 'Basisvorlesung', 'parent' => 'g_vorlesung'],
            'ringvorlesung'         => ['visible' => 1, 'name' => 'Ringvorlesung', 'parent' => 'g_vorlesung'],
        'g_seminar'             => ['visible' => 1, 'name' => 'Seminar', 'parent' => ''],
            'seminar'               => ['visible' => 1, 'name' => 'Seminar', 'parent' => 'g_seminar'],
            'einfuehrungsseminar'   => ['visible' => 1, 'name' => 'Einführungsseminar', 'parent' => 'g_seminar'],
            'proseminar'            => ['visible' => 1, 'name' => 'Proseminar', 'parent' => 'g_seminar'],
            'hauptseminar'          => ['visible' => 1, 'name' => 'Hauptseminar', 'parent' => 'g_seminar'],
            'obersemminar'          => ['visible' => 1, 'name' => 'Oberseminar', 'parent' => 'g_seminar'],
        'g_uebung'              => ['visible' => 1, 'name' => 'Übung/Tutorium/Sprache', 'parent' => ''],
            'uebung'                => ['visible' => 1, 'name' => 'Übung', 'parent' => 'g_uebung'],
            'tutorium'              => ['visible' => 1, 'name' => 'Tutorium', 'parent' => 'g_uebung'],
            'sprachkurs'            => ['visible' => 1, 'name' => 'Sprachkurs', 'parent' => 'g_uebung'],
            'sprachuebung'          => ['visible' => 1, 'name' => 'Sprachübung', 'parent' => 'g_uebung'],
            'lektuereuebung'        => ['visible' => 1, 'name' => 'Lektüreübung', 'parent' => 'g_uebung'],
            'uebersetzungsuebung'   => ['visible' => 1, 'name' => 'Übersetzungsübung', 'parent' => 'g_uebung'],
            'stiluebung'            => ['visible' => 1, 'name' => 'Stilübung', 'parent' => 'g_uebung'],
        'g_kolloquium'          => ['visible' => 1, 'name' => 'Kolloquium', 'parent' => ''],
            'kolloquium'            => ['visible' => 1, 'name' => 'Kolloquium', 'parent' => 'g_kolloquium'],
            'forschungskolloquium'  => ['visible' => 1, 'name' => 'Forschungskolloquium', 'parent' => 'g_kolloquium'],
            'examenskolloquium'     => ['visible' => 1, 'name' => 'Examenskolloquium', 'parent' => 'g_kolloquium'],
        'g_praxis'              => ['visible' => 1, 'name' => 'Praxis/Exkursion', 'parent' => ''],
            'exkursion'             => ['visible' => 1, 'name' => 'Exkursion', 'parent' => 'g_praxis'],
            'praktischerkurs'       => ['visible' => 1, 'name' => 'Praktischer Kurs', 'parent' => 'g_praxis'],
            'sportpraktischerkurs'  => ['visible' => 1, 'name' => 'Sportpraktischer Kurs', 'parent' => 'g_praxis'],
            'kuenstlerischerunterricht' => ['visible' => 1, 'name' => 'Künstlerischer Unterricht', 'parent' => 'g_praxis'],
            'praktikum'             => ['visible' => 1, 'name' => 'Praktikum', 'parent' => 'g_praxis'],
        'g_projekt'             => ['visible' => 1, 'name' => 'Projekt', 'parent' => ''],
            'projekt'               => ['visible' => 1, 'name' => 'Projekt', 'parent' => 'g_projekt'],
            'projektseminar'        => ['visible' => 1, 'name' => 'Projektseminar', 'parent' => 'g_projekt'],
            'projektpraktikum'      => ['visible' => 1, 'name' => 'Projektpraktikum', 'parent' => 'g_projekt'],
            'szenischesprojekt'     => ['visible' => 1, 'name' => 'Szenisches Projekt', 'parent' => 'g_projekt'],
        'g_sonstiges'           => ['visible' => 1, 'name' => 'Sonstiges', 'parent' => ''],
            'freielektuere'         => ['visible' => 1, 'name' => 'Freie Lektüre', 'parent' => 'g_sonstiges'],
            'grundkurs'             => ['visible' => 1, 'name' => 'Grundkurs', 'parent' => 'g_sonstiges'],
            'klausur'               => ['visible' => 1, 'name' => 'Klausur', 'parent' => 'g_sonstiges'],
            'kursus'                => ['visible' => 1, 'name' => 'Kursus', 'parent' => 'g_sonstiges'],
            'modul'                 => ['visible' => 1, 'name' => 'Modul', 'parent' => 'g_sonstiges'],
            'repetitorium'          => ['visible' => 1, 'name' => 'Repetitorium', 'parent' => 'g_sonstiges'],
            'selbstaendigebetreutearbeit' => ['visible' => 1, 'name' => 'Selbständige betreute Arbeit', 'parent' => 'g_sonstiges'],
            'workshop'              => ['visible' => 1, 'name' => 'Workshop', 'parent' => 'g_sonstiges'],
        'g_kombinationen'       => ['visible' => 1, 'name' => 'Kombinationen', 'parent' => ''],
            'vorlesungseminar'      => ['visible' => 1, 'name' => 'Vorlesung oder Seminar', 'parent' => 'g_kombinationen'],
            'vorlesungproseminar'   => ['visible' => 1, 'name' => 'Vorlesung oder Proseminar', 'parent' => 'g_kombinationen'],
            'vorlesunghauptseminar' => ['visible' => 1, 'name' => 'Vorlesung oder Hauptseminar', 'parent' => 'g_kombinationen'],
            'vorlesunguebung'       => ['visible' => 1, 'name' => 'Vorlesung oder Übung', 'parent' => 'g_kombinationen'],
            'vorlsemlektuere'       => ['visible' => 1, 'name' => 'Vorlesung, Seminar oder Lektüreübung', 'parent' => 'g_kombinationen'],
            'vorlsemeinf'           => ['visible' => 1, 'name' => 'Vorlesung, Seminar oder Einführungsveranstaltungen', 'parent' => 'g_kombinationen'],
            'vorlsemeinflektuere'   => ['visible' => 1, 'name' => 'Vorlesung, Seminar, Einführung oder Lektüreübung', 'parent' => 'g_kombinationen'],
            'seminaroberseminar'    => ['visible' => 1, 'name' => 'Seminar oder Oberseminar', 'parent' => 'g_kombinationen'],
            'seminarprojektseminar' => ['visible' => 1, 'name' => 'Seminar oder Projektseminar', 'parent' => 'g_kombinationen'],
            'seminaruebung'         => ['visible' => 1, 'name' => 'Seminar oder Übung', 'parent' => 'g_kombinationen'],
            'seminarlektuere'       => ['visible' => 1, 'name' => 'Seminar oder Lektürekurs', 'parent' => 'g_kombinationen'],
            'seminarszenischesprojekt' => ['visible' => 1, 'name' => 'Seminar oder Szenisches Projekt', 'parent' => 'g_kombinationen'],
            'seminaruebungexkursion' => ['visible' => 1, 'name' => 'Seminar, Übung oder Exkursion', 'parent' => 'g_kombinationen'],
            'semszenprojektkuenstleistung' => ['visible' => 1, 'name' => 'Seminar, Szenisches Projekt oder eigene Künstlerische Leistung', 'parent' => 'g_kombinationen'],
            'uebungprojekt'        => ['visible' => 1, 'name' => 'Übung oder Projekt', 'parent' => 'g_kombinationen'],
            'uebungtutorium'       => ['visible' => 1, 'name' => 'Übung oder Tutorium', 'parent' => 'g_kombinationen'],
            'uebunglektuerekurs'   => ['visible' => 1, 'name' => 'Übung oder Lektürekurs', 'parent' => 'g_kombinationen'],
            'szenprojektkuenstleistung' => ['visible' => 1, 'name' => 'Szenisches Projekt oder eigene künstlerische Leistung', 'parent' => 'g_kombinationen'],
            'praktkursfestivalorga' => ['visible' => 1, 'name' => 'Praktischer Kurs oder Festivalorganisation', 'parent' => 'g_kombinationen'],
    ],
    'default'           => ''
];

// Modulteildeskriptor
$GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE'] = $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE'];

// Maximale Anzahl Fachsemester
$GLOBALS['MVV_MODULTEIL_FACHSEMESTER'] = 10;

// Status des zugeordneten Fachsemesters
$GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS'] = [
    'values' => [
        'kann' => [
            'visible' => 1,
            'name'    => _('kann'),
            'icon'    => 'o'],
        'soll' => [
            'visible' => 1,
            'name'    => _('soll'),
            'icon'    => '+'],
        'muss' => [
            'visible' => 1,
            'name'    => _('muss'),
            'icon'    => '#']
    ],
    'default' => ''
];

$GLOBALS['MVV_STGTEILVERSION']['STATUS'] = $GLOBALS['MVV_MODUL']['STATUS'];

$GLOBALS['MVV_STGTEIL']['STATUS'] = $GLOBALS['MVV_MODUL']['STATUS'];

$GLOBALS['MVV_LANGUAGES'] = [
    'values'            => [
        'DE'                => ['visible' => 1, 'name' => _('Deutsch'),
                                'locale' => 'de_DE'],
        'EN'                => ['visible' => 1, 'name' => _('Englisch'),
                                'locale' => 'en_GB']
    ],
    'default'           => 'DE'
];

$GLOBALS['MVV_STUDIENGANG']['FASSUNG_TYP'] = $GLOBALS['MVV_STGTEILVERSION']['FASSUNG_TYP'];
