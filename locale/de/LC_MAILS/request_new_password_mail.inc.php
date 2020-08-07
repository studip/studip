<?
    $subject = _("[Stud.IP - " . Config::get()->UNI_NAME_CLEAN . "] Neues Passwort setzen (Schritt 3 von 5)");

    $mailbody="Dies ist eine Bestätigungsmail des Stud.IP-Systems\n"
    ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
    ."- ".Config::get()->UNI_NAME_CLEAN." -\n\n"
    ."Sie haben um die Zurücksetzung Ihres Passwortes gebeten.\n\n"
    ."Diese E-Mail wurde Ihnen zugesandt um sicherzustellen,\n"
    ."dass die angegebene E-Mail-Adresse tatsächlich Ihnen gehört.\n\n"
    ."Wenn Sie um die Zurücksetzung Ihres Passwortes gebeten haben, dann öffnen Sie bitte folgenden Link\n\n"
    ."{$GLOBALS['ABSOLUTE_URI_STUDIP']}request_new_password.php?id={$id}&cancel_login=1\n\n"
    ."in Ihrem Browser. Auf der Seite können Sie ein neues Passwort setzen.\n\n"
    ."Wahrscheinlich unterstützt Ihr E-Mail-Programm ein einfaches Anklicken des Links.\n"
    ."Ansonsten müssen Sie Ihren Browser öffnen und den Link komplett in die Zeile\n"
    ."\"Location\" oder \"URL\" kopieren.\n\n"
    ."Falls Sie sich nicht als Benutzer \"{$username}\" angemeldet haben\n"
    ."oder überhaupt nicht wissen, wovon hier die Rede ist,\n"
    ."dann hat jemand Ihre E-Mail-Adresse fälschlicherweise verwendet!\n"
    ."Ignorieren Sie in diesem Fall diese E-Mail. Es werden dann keine Änderungen an\n"
    ."Ihren Zugangsdaten vorgenommen.\n\n";
?>
