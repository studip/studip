<form action="<?= $controller->link_for('terms', compact('return_to', 'redirect_token')) ?>" method="post">
    <?= CSRFProtection::tokenTag()?>
    <? if ($denial_message): ?>
        <section class="contentbox">
            <header>
                <h1><?= _('Was können Sie tun?') ?></h1>
            </header>
            <section>
                <?= $denial_message ?>
            </section>
        </section>
    <? else: ?>
        <?= $GLOBALS['template_factory']->render('terms.php') ?>
    <? endif; ?>
    <footer style="text-align: center">
    <? if ($denial_message): ?>
        <?= Studip\LinkButton::createAccept(
            _('Verstanden'),
            URLHelper::getURL('logout.php')
        ) ?>
    <? else: ?>
        <?= Studip\Button::createAccept(_('Ich erkenne die Nutzungsbedingungen an'), 'accept') ?>

        <? if (!$compulsory): ?>
        <?= Studip\LinkButton::createCancel(
            _('Ich stimme den Nutzungsbedingungen nicht zu'),
            $controller->url_for('terms', ['action' => 'denied'])
        ) ?>
        <? endif; ?>
    <? endif; ?>
    </footer>
</form>
