<?
$subject = "Anmeldung Stud.IP-System";

$mailbody = "Dies ist eine Informationsmail des Stud.IP-Systems.\n"
    . "Stud.IP ist eine Online-Plattform, die als studienbegleitende Unterstützung der Präsenzlehre genutzt wird.\n\n"
    . "Betrieben wird diese Plattform von: " . Config::get()->UNI_NAME_CLEAN . "\n\n"
    . "Sie wurden um $Zeit mit folgenden Angaben von einem der Administrierenden in das System eingetragen.\n"
    . "Mit dieser E-Mail erhalten Sie Ihren Benutzernamen und Ihr Passwort.\n"
    . "Die für Sie in der Datenbank hinterlegten Daten sind folgende:\n\n"
    . "Benutzername: " . $this->user_data['auth_user_md5.username'] . "\n"
    . "Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
    . "Vorname: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
    . "Nachname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
    . "E-Mail-Adresse: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
    . "Mit diesen Anmeldedaten können Sie sich nun auf der Startseite im Stud.IP-System anmelden.\n"
    . "Dazu besuchen Sie bitte:\n\n"
    . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n"
    . "Wahrscheinlich unterstützt Ihr E-Mail-Programm ein einfaches Anklicken des Links.\n"
    . "kopieren Sie bitte den Link, öffnen Ihren Browser und fügen diesen in die Adresszeile\n"
    . "\"Location\" oder \"URL\" ein.\n\n"
    . "Um Zugang auf die nichtöffentlichen Bereiche des Systems zu bekommen,\n"
    . "müssen Sie sich unter \"Login\" auf der Seite anmelden.\n"
    . "Geben Sie bitte unter Benutzername \"" . $this->user_data['auth_user_md5.username'] . "\" und unter Passwort: \"" . $password . "\" ein. \n\n"
    . "Das Passwort ist nur Ihnen bekannt, bitte geben Sie es niemals an Dritte weiter.\n\n"
    . "Mit besten Grüßen,\n\n"
    . "Ihr Stud.IP Supportteam\n";
?>
