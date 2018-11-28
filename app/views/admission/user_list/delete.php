<?= createQuestion(sprintf(_('Soll die Nutzerliste %s wirklich gelöscht werden?'), 
    $list->getName()), array('really' => true), array('cancel' => true), 
    $controller->url_for('admission/userlist/delete', $userlist->getId()));
?>
