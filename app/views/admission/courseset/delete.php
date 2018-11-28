<?= createQuestion(sprintf(_('Sind Sie sicher, dass das Anmeldeset "%s" '.
    'gelöscht werden soll? Damit werden alle Regeln zur Anmeldung zu den '.
    'verknüpften Veranstaltungen aufgehoben.'), $courseset->getName()), 
    array('really' => true), array('cancel' => true), 
    $controller->url_for('admission/courseset/delete', $courseset->getId()));
?>
