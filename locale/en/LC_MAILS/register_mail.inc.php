<?
        $subject="Stud.IP system confirmation mail";
        
        $mailbody="This e-mail has been generated automatically by the Stud.IP System.\n"
        ."Stud.IP is a study-related online platform,\n"
        ."used by the" . Config::get()->UNI_NAME_CLEAN . ", to support presence teaching.\n"
        ."This email contains your user data.\n\n"
        ."One of the Stud.IP-Admins recently generated your account.\n"
        ."You have been added with the following data:\n\n"
        ."Username: $username\n"
        . $password ? "Password: $password\n" : ''
        . $status ? "Status: $status\n" : ''
        ."Name: $Vorname\n"
        ."Surname: $Nachname\n"
        ."E-mail address: $Email\n\n"
        ."To visit Stud.IP, please go to the following url:\n\n"
        ."$url\n\n"
        ."If your e-mail client does not support HTML-directlinks,\n"
        ."please copy the full path and insert it into the adress line of your browser.\n\n"
        ."To login to Stud.IP, please use the login-button and enter your |username| and your |password|\n"
        ." To switch your language setting within Stud.IP login and select Einstellungen.\n"
        ."The first drop-down menu is for language settings.\n\n"
        ."Your login data is only yours to know, please never hand this data to any third parties!\n\n"
        ."Kind regards,\n\n"
        ."your Stud.IP support-team\n"
?>
