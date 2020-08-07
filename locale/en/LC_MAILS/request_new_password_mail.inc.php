<?
    $subject = _("[Stud.IP - " . $GLOBALS['UNI_NAME_CLEAN'] . "] Set new password (STEP 3/5)");

    $mailbody="This is a Stud.IP system confirmation mail.\n"
    ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
    ."- " . Config::get()->UNI_NAME_CLEAN . "-\n\n"
    ."You requested setting a new password for your account.\n"
    ."This mail is being sent to you to be sure,\n"
    ."that the given E-mail address does actually belong to you.\n\n"
    ."If you indeed requested a password reset, please open the following link\n\n"
    ."{$GLOBALS['ABSOLUTE_URI_STUDIP']}request_new_password.php?id={$id}&cancel_login=1\n\n"
    ."in your browser. You will be able to set a new password on that page.\n\n"
    ."Your mail program will probably support a simple click on the link.\n"
    ."If not, you must open your browser and copy the link completely into the line\n"
    ."\"Location\" oder \"URL\".\n\n"
    ."You must login as user \"$username\",\n"
    ."so that the re-confirmation can work.\n\n"
    ."If you have not registered as user \"$username\",\n"
    ."or have no idea what is being talked about here,\n"
    ."then someone has been abusing your E-mail address!\n\n"
    ."In this case ignore the e-mail and your password will not be changed.\n";
?>
