<?
        $subject = "Neuer Administrator in Ihrer Einrichtung angelegt";
        
        $mailbody = sprintf("Liebe(r) %s %s,\n\n"
        ."In der Einrichtung '%s' wurde %s %s als Administrator eingetragen und steht Ihnen als neuer Ansprechpartner bei Fragen oder Problemen im StudIP zur Verfügung. ",$db->f('Vorname'),$db->f('Nachname'),$instname,$vorname,$nachname);
        
?>
