<?= createQuestion(sprintf(_('Soll die Nutzerliste %s wirklich gelöscht werden?'), 
    $list->getName()), ['really' => true], ['cancel' => true], 
    $controller->url_for('admission/userlist/delete', $userlist->getId()));
?>
