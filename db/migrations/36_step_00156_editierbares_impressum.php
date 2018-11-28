<?php

/*
 * Copyright (C) 2008 - Ansgar Bockstiegel <ansgar.bockstiegel@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Step00156EditierbaresImpressum extends Migration {


  function description() {
    return 'Adds two new tables and fills them with default content.';
  }


  function up() {
    $db = DBManager::get();

    $this->announce("add new table siteinfo_rubrics");
    $db->exec("CREATE TABLE IF NOT EXISTS `siteinfo_rubrics` (
                  `rubric_id` smallint(5) unsigned NOT NULL auto_increment,
                  `position` tinyint(3) unsigned default NULL,
                  `name` varchar(255) NOT NULL,
                  PRIMARY KEY  (`rubric_id`)) ENGINE=MyISAM");

    $this->announce("fill siteinfo_rubrics with default content");
    $db->exec("INSERT INTO `siteinfo_rubrics` (`rubric_id`, `name`)
                 VALUES
                    (1, '[lang=de]Kontakt[/lang][lang=en]Contact[/lang]'),
                    (2, '[lang=de]Über Stud.IP[/lang][lang=en]About Stud.IP[/lang]')");

    $this->announce("add new table siteinfo_details");
    $db->exec("CREATE TABLE IF NOT EXISTS `siteinfo_details` (
                `detail_id` smallint(5) unsigned NOT NULL auto_increment,
                `rubric_id` smallint(5) unsigned NOT NULL,
                `position` tinyint(3) unsigned default NULL,
                `name` varchar(255) NOT NULL,
                `content` text NOT NULL,
                PRIMARY KEY  (`detail_id`)) ENGINE=MyISAM");

    $this->announce("fill siteinfo_details with default content");
    $db->exec("INSERT INTO `siteinfo_details` (`rubric_id`, `name`, `content`) 
               VALUES
                    (1,
                     '[lang=de]Ansprechpartner[/lang][lang=en]Contact[/lang]',
                     '[style=float: right]".'\n'.
                     "[img]http://www.studip.de/images/studipanim.gif".'\n'.
                     "**Version:** (:version:)".'\n'.
                     "[/style]".'\n'.
                     "[lang=de]Für diese Stud.IP-Installation ((:uniname:)) sind folgende Administratoren zuständig:[/lang]".'\n'.
                     "[lang=en]The following administrators are responsible for this Stud.IP installation ((:uniname:)):[/lang]".'\n'.
                     "(:rootlist:)".'\n'.
                     "[lang=de]allgemeine Anfragen wie Passwort-Anforderungen u.a. richten Sie bitte an:[/lang]".'\n'.
                     "[lang=en]General queries e.g., password queries, please contact:[/lang]".'\n'.
                     "(:unicontact:)".'\n'.
                     "[lang=de]Folgende Einrichtungen sind beteiligt:".'\n'.
                     "(Genannt werden die jeweiligen Administratoren der Einrichtungen für entsprechende Anfragen)[/lang]".'\n'.
                     "[lang=en]The following institutes participate:".'\n'.
                     "(Named are the institutes administrators responsible for the corresponding query areas)[/lang]".'\n'.
                     "(:adminlist:)'),
                    (1,
                     '[lang=de]Entwickler[/lang][lang=en]Developer[/lang]',
                     '[style=float: right]".'\n'.
                     "[img]http://www.studip.de/images/studipanim.gif".'\n'.
                     "**Version:** (:version:)".'\n'.
                     "[/style]".'\n'.
                     "[lang=de]Stud.IP ist ein Open Source Projekt zur Unterstützung von Präsenzlehre an Universitäten, Hochschulen und anderen Bildungseinrichtungen. Das System entstand am Zentrum für interdisziplinäre Medienwissenschaft (ZiM) der Georg-August-Universität Göttingen unter Mitwirkung der Suchi & Berg GmbH (data-quest) , Göttingen. Heute erfolgt die Weiterentwicklung von Stud.IP verteilt an vielen Standorten (Göttingen, Osnabrück, Oldenburg, Bremen, Hannover, Jena und weiteren). Die Koordination der Entwicklung erfolgt durch die Stud.IP-CoreGroup.".'\n'.
                     "Stud.IP steht unter der GNU General Public License, Version 2.".'\n\n'.
                     "Weitere Informationen finden Sie auf [**www.studip.de**]http://www.studip.de , [**develop.studip.de**]http://develop.studip.de und [**blog.studip.de**]http://blog.studip.de.[/lang]".'\n\n'.
                     "[lang=en]Stud.IP is an opensource project for supporting attendance courses offered by universities, institutions of higher education and other educational institutions. The system was established at the Zentrum für interdisziplinäre Medienwissenschaft (ZiM) in the Georg-August-Universität Göttingen in cooperation with Suchi & Berg GmbH (data-quest) , Göttingen. At the present further developing takes place at various locations (among others Göttingen, Osnabrück, Oldenburg, Bremen, Hannover, Jena) under coordination through the Stud.IP-CoreGroup.".'\n\n'.
                     "Stud.IP is covered by the GNU General Public Licence, version 2.".'\n\n'.
                     "Further information can be found under [**www.studip.de**]http://www.studip.de , [**develop.studip.de**]http://develop.studip.de and [**blog.studip.de**]http://blog.studip.de.[/lang]".'\n\n'.
                     "(:coregroup:)".'\n'.
                     "[lang=de]Sie erreichen uns auch über folgende **Mailinglisten**:".'\n\n'.
                     "**Nutzer-Anfragen**, E-Mail: studip-users@lists.sourceforge.net: Fragen, Anregungen und Vorschläge an die Entwickler - bitte __keine__ Passwort Anfragen!".'\n'.
                     "**News-Mailingsliste**, E-Mail: studip-news@lists.sourceforge.net: News rund um Stud.IP (Eintragung notwendig)".'\n\n'.
                     "Wir laden alle Entwickler, Betreiber und Nutzer von Stud.IP ein, sich auf dem Developer-Server http://develop.studip.de an den Diskussionen rund um die Weiterentwicklung und Nutzung der Plattform zu beteiligen.[/lang]".'\n'.
                     "[lang=en]You can contact us via the following **mailing lists**:".'\n\n'.
                     "**User enquiries**, E-Mail: studip-users@lists.sourceforge.net: Questions, suggestions and recommendations to the developers - __please no password queries__!".'\n\n'.
                     "**News mailing list**, E-Mail: studip-news@lists.sourceforge.net: News about Stud.IP (registration necessary)".'\n\n'.
                     "We invite all developers, administrators and users of Stud.IP to join the discussions on further developing and using the platform available at the developer server http://develop.studip.de[/lang]'),
                    (2,
                     '[lang=de]Technik[/lang][lang=en]Technology[/lang]',
                     '[style=float: right]".'\n'.
                     "[img]http://www.studip.de/images/studipanim.gif".'\n'.
                     "**Version:** (:version:)".'\n'.
                     "[/style]".'\n'.
                     "[lang=de]Stud IP ist ein Open-Source Projekt und steht unter der GNU General Public License. Sämtliche zum Betrieb notwendigen Dateien können unter http://sourceforge.net/projects/studip/ heruntergeladen werden.".'\n'.
                     "Die technische Grundlage bietet ein LINUX-System mit Apache Webserver sowie eine MySQL Datenbank, die über PHP gesteuert wird.".'\n'.
                     "Im System findet ein 6-stufiges Rechtesystem Verwendung, das individuell auf verschiedenen Ebenen wirkt - etwa in Veranstaltungen, Einrichtungen, Fakultäten oder systemweit.".'\n'.
                     "Seminare oder Arbeitsgruppen können mit Passwörtern geschützt werden - die Verschlüsselung erfolgt mit einem MD5 one-way-hash.".'\n'.
                     "Das System ist zu 100% über das Internet administrierbar, es sind keine zusätzlichen Werkzeuge nötig. Ein Webbrowser der 5. Generation wird empfohlen.".'\n'.
                     "Das System wird ständig weiterentwickelt und an die Wünsche unserer Nutzer angepasst - [sagen Sie uns Ihre Meinung!]studip-users@lists.sourceforge.net[/lang]".'\n'.
                     "[lang=en]Stud.IP is an Open Source Project and is covered by the Gnu General Public License (GPL). All files necessary for operation can be downloaded from http://sourceforge.net/projects/studip/ .".'\n'.
                     "The technical basis can be provided by a LINUX system with Apache Webserver and a MySQL database, which is then controlled by PHP.".'\n'.
                     "The system features a authorisation system with six ranks, that affects individually different levels - in courses, institutes,faculties or system wide.".'\n'.
                     "Seminars or work groups can be secured with passwords - the encryption of which uses a MD5 one-way-hash.".'\n'.
                     "The system is capable of being administrated 100% over the internet - no additional tools are necessary. A 5th generation web browser is recommended.".'\n'.
                     "The system is continually being developed and customised to the wishes of our users - [Tell us your opinion!]studip-users@lists.sourceforge.net[/lang]'),
                    (2,
                     '[lang=de]Statistik[/lang][lang=en]Statistics[/lang]', 
                     '[lang=de]!!Top-Listen aller Veranstaltungen[/lang]".
                     "[lang=en]!!Top list of all courses[/lang]".'\n'.
                     "[style=float: right]".'\n'.
                     "[lang=de]!!Statistik[/lang][lang=en]!!statistics[/lang]".'\n'.
                     "(:indicator seminar_all:)".'\n'.
                     "(:indicator seminar_archived:)".'\n'.
                     "(:indicator institute_firstlevel_all:)".'\n'.
                     "(:indicator institute_secondlevel_all:)".'\n'.
                     "(:indicator user_admin:)".'\n'.
                     "(:indicator user_dozent:)".'\n'.
                     "(:indicator user_tutor:)".'\n'.
                     "(:indicator user_autor:)".'\n'.
                     "(:indicator posting:)".'\n'.
                     "(:indicator document:)".'\n'.
                     "(:indicator link:)".'\n'.
                     "(:indicator litlist:)".'\n'.
                     "(:indicator termin:)".'\n'.
                     "(:indicator news:)".'\n'.
                     "(:indicator guestbook:)".'\n'.
                     "(:indicator vote:)".'\n'.
                     "(:indicator test:)".'\n'.
                     "(:indicator evaluation:)".'\n'.
                     "(:indicator wiki_pages:)".'\n'.
                     "(:indicator lernmodul:)".'\n'.
                     "(:indicator resource:)".'\n'.
                     "[/style]".'\n'.
                     "(:toplist mostparticipants:)".'\n'.
                     "(:toplist recentlycreated:)".'\n'.
                     "(:toplist mostdocuments:)".'\n'.
                     "(:toplist mostpostings:)".'\n'.
                     "(:toplist mostvisitedhomepages:)'),
                    (2,
                     'History',
                     '(:history:)'),
                    (2,
                     'Stud.IP-Blog',
                     '[lang=de]Das Blog der Stud.IP-Entwickler finden Sie auf:[/lang]".'\n'.
                     "[lang=en]The Stud.IP-Developer-Blog can be found under:[/lang]".'\n'.
                     "http://blog.studip.de')");

    $this->announce("done.");
  }


  function down() {
    $db = DBManager::get();

    $this->announce("remove siteinfo_details");
    $db->exec("DROP table `siteinfo_details`");

    $this->announce("remove siteinfo_rubrics");
    $db->exec("DROP table `siteinfo_rubrics`");

    $this->announce("done.");
  }
}
