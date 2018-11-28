<?
        $subject="Registration in the Stud.IP system";
        
        $mailbody="This is a Stud.IP system information mail\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . " -\n\n"
        ."The following information was entered into the system\n"
        ."by an administrator at " . $Zeit . ":\n\n"
        ."Username: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Password: " . $password . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Forename: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Surname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-mail address: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."This mail was sent to inform you about your username and password\n"
        ."so that you can log on into the system.\n\n"
        ."You will find the system start page under the following URL:\n\n"
        . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n"
        ."Your mail program will probably support a simple click on the link.\n"
        ."If not, you must open your browser and copy the link completely into the line\n"
        ."\"Location\" oder \"URL\".\n\n"
        ."In order to gain access to the non-public areas of the system\n"
        ."you must register under \"Login\".\n"
        ."Please enter  \"" . $this->user_data['auth_user_md5.username'] . "\" as username\n"
        ."and \"" . $password . "\" as password.\n\n"
        ."The password is only known to you. Please do not pass it onto anyone\n"
        ."(not even an administrator), so that a third party cannot post\n"
        ."messages in the system under your name!\n\n";

?>
