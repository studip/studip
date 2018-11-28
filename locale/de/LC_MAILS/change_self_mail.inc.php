<?
        $subject="E-Mail-Änderung Stud.IP";

        $mailbody="Dies ist eine Informationsmail des Systems Stud.IP\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- ". Config::get()->UNI_NAME_CLEAN ." -\n\n"
        ."Um die Änderung Ihrer E-Mail Adresse abzuschließen,\n"
        ."folgen Sie bitte dem nachfolgendem Link:\n"
        . $activatation_url . "\n\n"
        ."Der Aktivierungsschlüssel lautet:\n"
        . $key . "\n\n"

?>
