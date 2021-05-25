<?
        $subject="Stud.IP system account modification";

        $mailbody="This is a Stud.IP system information mail\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."Your account was modified ".($this->user_data['auth_user_md5.locked']==1 ? "and locked" : "")." by the system at $Zeit\n"
        ."The current information is:\n\n"
        ."Username: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Forename: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Surname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-mail-address: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."Your password has not been changed.\n\n"
        ."This mail has been sent to you, to inform you of the changes.\n\n"
        ."If you have objections against these changes, please contact\n"
        . $this->abuse_email . "\n"
        ."You can simply reply to this mail.\n\n"
        ."Here takes you directly into the system:\n"
        . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n";

?>
