<?
        $subject="Account-Änderung Stud.IP-System";
        
        $mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."Ihr Account wurde um " . $Zeit . " von der Administration verändert"
        .($this->user_data['auth_user_md5.locked']==1 ? " und gesperrt" : "")
        .".\nDie aktuellen Angaben lauten:\n\n"
        ."Benutzername: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Vorname: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Nachname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-Mail-Adresse: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."Ihr Passwort hat sich nicht verändert.\n\n"
        ."Diese E-Mail wurde Ihnen zugesandt, um Sie über die Änderungen zu informieren.\n\n"
        ."Wenn Sie Einwände gegen die Änderungen haben, wenden Sie sich bitte an\n"
        . $this->abuse_email . "\n"
        ."Sie können einfach auf diese E-Mail antworten.\n\n"
        ."Hier kommen Sie direkt ins System:\n"
        . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n";

?>
