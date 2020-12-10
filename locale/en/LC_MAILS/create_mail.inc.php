<?
$subject = "Registration in the Stud.IP system";

$mailbody = "This e-mail has been generated automatically by the Stud.IP System.\n"
    . "Stud.IP is a study-related online platform, to support presence teaching.\n"
    . "This platform is operated by: " . Config::get()->UNI_NAME_CLEAN . "\n"
    . "This email contains your user data.\n\n"
    . "One of the Stud.IP-Admins generated your account at " . $Zeit . "\n"
    . "You have been added with the following data:\n\n"
    . "Username: " . $this->user_data['auth_user_md5.username'] . "\n"
    . "Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
    . "Forename: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
    . "Surname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
    . "E-mail address: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
    . "To visit Stud.IP, please go to the following url:\n\n"
    . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n"
    . "If your e-mail client does not support HTML-directlinks,\n"
    . "please copy the full path and insert it into the adress line of your browser.\n\n"
    . "To login to Stud.IP, please use the login-button and enter your username \"" . $this->user_data['auth_user_md5.username'] . "\" and your password \"" . $password . "\"\n"
    . " To switch your language setting within Stud.IP login and select Einstellungen.\n"
    . "The first drop-down menu is for language settings.\n\n"
    . "Your login data is only yours to know, please never hand this data to any third parties!\n\n"
    . "Kind regards,\n\n"
    . "your Stud.IP support-team\n";

?>
