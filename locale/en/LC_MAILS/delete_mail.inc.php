<?
        $subject="Account deletion in the Stud.IP-System";
        
        $mailbody="This is a Stud.IP system information mail\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."Your account\n\n"
        ."Username: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Forename: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Surname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-mail address: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."was deleted by an administrator at " . $Zeit . ".\n";

?>
