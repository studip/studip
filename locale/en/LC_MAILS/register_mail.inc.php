<?
        $subject="Stud.IP system confirmation mail";

        $mailbody="This is a Stud.IP system confirmation mail.\n"
        ."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
        ."- " . Config::get()->UNI_NAME_CLEAN . "-\n\n"
        ."You registered with the following information at $Zeit:\n\n"
        ."Username: $username\n"
        ."Forename: $Vorname\n"
        ."Surname: $Nachname\n"
        ."E-mail address: $Email\n\n"
        ."This mail is being sent to you to be sure,\n"
        ."that the given E-mail address does actually belong to you.\n\n"
        ."If this information is correct, please open the link\n\n"
        ."$url\n\n"
        ."in your browser.\n"
        ."Your mail program will probably support a simple click on the link.\n"
        ."If not, you must open your browser and copy the link completely into the line\n"
        ."\"Location\" oder \"URL\".\n\n"
        ."You must login as user \"$username\",\n"
        ."so that the re-confirmation can work.\n\n"
        ."If you have not registered as user \"$username\",\n"
        ."or have no idea what is being talked about here,\n"
        ."then someone has been using your mail address by mistake!\n\n"
        ."In this case, please contact $abuse,\n"
        ."so that the entry can be deleted from the database.\n";
?>