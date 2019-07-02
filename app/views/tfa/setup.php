<form class="default" action="<?= $controller->create() ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Zwei-Faktor-Authentisierung einrichten') ?></legend>

        <p>
            <?= _('Mittels Zwei-Faktor-Authentisierung können Sie Ihr Konto schützen, '
                . 'indem bei jedem Login ein Token von Ihnen eingegeben werden muss.') ?>
            <?= _('Dieses Token erhalten Sie entweder per E-Mail oder können es über '
                . 'eine geeignete Authenticator-App erzeugen lassen.') ?>
        </p>

        <label>
            <input required type="radio" name="type" value="email">
            <?= _('E-Mail') ?>
        </label>

        <label>
            <input required type="radio" name="type" value="app">
            <?= _('Authenticator-App') ?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Aktivieren')) ?>
    </footer>
</form>
