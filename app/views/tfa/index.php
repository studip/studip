<p>
    <?= _('Zwei-Faktor-Authentifizierung ist aktiviert') ?>:
    <?= $secret->type == 'app' ? _('Authenticator-App') : _('E-Mail') ?>
</p>
<form action="<?= $controller->revoke() ?>" method="post">
    <?= Studip\Button::createAccept(_('Aufheben')) ?>
</form>
