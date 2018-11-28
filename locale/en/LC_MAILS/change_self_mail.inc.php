<?
        $subject="email address change in Stud.IP";

        $mailbody="This is a Stud.IP system information mail\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- ". Config::get()->UNI_NAME_CLEAN ." -\n\n"
        ."To complete the process of changing your email address\n"
        ."please follow this link:\n"
        . $activatation_url . "\n\n"
        ."Your activation key is:\n"
        . $key . "\n\n"

?>
