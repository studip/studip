<?
        $subject="Bestätigungsmail des Stud.IP-Systems";
        
        $mailbody="Dies ist eine Bestätigungsmail des Systems Stud.IP\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . "-\n\n"
        ."Sie haben sich um $Zeit mit folgenden Angaben angemeldet:\n\n"
        ."Benutzername: $username\n"
        ."Vorname: $Vorname\n"
        ."Nachname: $Nachname\n"
        ."E-Mail-Adresse: $Email\n\n"
        ."Diese E-Mail wurde Ihnen zugesandt um sicherzustellen,\n"
        ."daß die angegebene E-Mail-Adresse tatsächlich Ihnen gehört.\n\n"
        ."Wenn diese Angaben korrekt sind, dann öffnen Sie bitte den Link\n\n"
        ."$url\n\n"
        ."in Ihrem Browser.\n"
        ."Wahrscheinlich unterstützt Ihr E-Mail-Programm ein einfaches Anklicken des Links.\n"
        ."Ansonsten müssen Sie Ihren Browser öffnen und den Link komplett in die Zeile\n"
        ."\"Location\" oder \"URL\" kopieren.\n\n"
        ."Sie müssen sich auf jeden Fall als BenutzerIn \"$username\" anmelden,\n"
        ."damit die Rückbestätigung funktioniert.\n\n"
        ."Falls Sie sich nicht als Benutzer \"$username\" angemeldet haben\n"
        ."oder überhaupt nicht wissen, wovon hier die Rede ist,\n"
        ."dann hat jemand Ihre E-Mail-Adresse fälschlicherweise verwendet!\n\n"
        ."Bitte wenden Sie sich in diesem Fall an $abuse,\n"
        ."damit der Eintrag aus der Datenbank gelöscht wird.\n";
?>
