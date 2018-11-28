<?
        $subject="Account-Löschung Stud.IP-System";
        
        $mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."Ihr Account\n\n"
        ."Benutzername: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Vorname: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Nachname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-Mail-Adresse: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."wurde um " . $Zeit . " von einem der Administrierenden gelöscht.\n";

?>
