<?= QuestionBox::create(
    sprintf(
        _('Soll die Nutzerliste %s wirklich gelöscht werden?'),
        htmlReady($list->getName())
    ),
    $controller->deleteURL($userlist->getId(), ['really' => true]),
    $controller->deleteURL($userlist->getId(), ['cancel' => true])
)
?>
