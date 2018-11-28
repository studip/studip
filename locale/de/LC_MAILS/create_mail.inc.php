<?
        $subject="Anmeldung Stud.IP-System";
        
        $mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."Sie wurden um " . $Zeit . " mit folgenden Angaben von einem\n"
        ."der Administrierenden in das System eingetragen:\n\n"
        ."Benutzername: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Passwort: " . $password . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Vorname: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Nachname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-Mail-Adresse: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."Diese Mail wurde Ihnen zugesandt, um Ihnen den Benutzernamen\n"
        ."und das Passwort mitzuteilen, mit dem Sie sich am System anmelden können.\n\n"
        ."Sie finden die Startseite des Systems unter folgender URL:\n\n"
        . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n"
        ."Wahrscheinlich unterstützt Ihr E-Mail-Programm ein einfaches Anklicken des Links.\n"
        ."Ansonsten müssen Sie Ihren Browser öffnen und den Link komplett in die Zeile\n"
        ."\"Location\" oder \"URL\" kopieren.\n\n"
        ."Um Zugang auf die nichtöffentlichen Bereiche des Systems zu bekommen\n"
        ."müssen Sie sich unter \"Login\" auf der Seite anmelden.\n"
        ."Geben Sie bitte unter Benutzername \"" . $this->user_data['auth_user_md5.username'] . "\" und unter\n"
        ."Passwort \"" . $password . "\" ein.\n\n"
        ."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
        ."weiter (auch nicht an eine Administratorin oder einen Administrator),\n"
        ."damit nicht Dritte in Ihrem Namen Nachrichten\n"
        ."in das System einstellen können!\n\n";

?>
