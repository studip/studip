<form action="<?= $controller->link_for('terms', compact('return_to', 'redirect_token')) ?>" method="post">
    <?= CSRFProtection::tokenTag()?>

    <?= $GLOBALS['template_factory']->render('terms.php') ?>

    <footer style="text-align: center">
        <?= Studip\Button::createAccept(_('Ich erkenne die Nutzungsbedingungen an'), 'accept') ?>
        <?= Studip\LinkButton::createCancel(
            _('Ich stimme den Nutzungsbedingungen nicht zu'),
            URLHelper::getURL('logout.php')
        ) ?>
    </footer>
</form>
