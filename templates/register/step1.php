<?= $GLOBALS['template_factory']->render('terms.php') ?>

<footer style="text-align: center">
    <?= Studip\LinkButton::createAccept(_('Ich erkenne die Nutzungsbedingungen an'), URLHelper::getLink('register2.php')) ?>
    <?= Studip\LinkButton::createCancel(_('Registrierung abbrechen'), URLHelper::getLink('index.php')) ?>
</footer>
