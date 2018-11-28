<?
        $subject="Password modification in the Stud.IP-System";
        
        $mailbody="This is a Stud.IP system information mail\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."Your password was changed by an administrator at " . $Zeit . ".\n"
        ."The current information is:\n\n"
        ."Username: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Password: " . $password . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Forename: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Surname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-mail address: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."The password is only known to you. Please do not pass it onto anyone\n"
        ."else (not even an administrator). This is to stop third parties\n"
        ."from posting messages in the system under your name!\n\n"
        ."Here takes you directly into the system:\n"
        . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n"

?>
