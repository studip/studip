<?= (string) QuestionBox::create(
    sprintf(_('Sind Sie sicher, dass das Anmeldeset "%s" ' .
        'gelöscht werden soll? Damit werden alle Regeln zur Anmeldung zu den ' .
        'verknüpften Veranstaltungen aufgehoben.'), htmlReady($courseset->getName())),
    $controller->deleteURL($courseset->getId(), ['really' => true]),
    $controller->deleteURL($courseset->getId(), ['cancel' => true])
) ?>
