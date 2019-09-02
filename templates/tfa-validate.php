<form action="<?= htmlReady(Request::url()) ?>" method="post" class="default">
    <input type="hidden" name="tfa-nonce" value="<?= htmlReady($__nonce) ?>">
    <fieldset>
        <legend><?= _('Zwei-Faktor-Authentifizierung') ?></legend>

<? if ($blocked): ?>
    <?= MessageBox::warning(_('Sie haben zu viele ungültige Versuche'), [sprintf(
        _('Versuchen Sie es in %u Minute(n) erneut'),
        ceil((time() - $blocked) / 60)
    )])->hideClose() ?>
<? else: ?>
        <p><?= htmlReady($text ?: _('Bitte geben Sie ein gültiges Token ein')) ?></p>
    <? if ($secret->type === 'app' && !$secret->confirmed): ?>
        <p>
            <?= _('Scannen Sie diesen Code mit Ihrer App ein und geben Sie '
                . 'anschliessend ein gültiges Token ein.') ?>
        </p>
        <div class="tfa-app-code">
            <code class="qr"><?= $secret->getProvisioningUri() ?></code>
        </div>
    <? endif; ?>

        <div class="tfa-code-input">
            <? ob_start(); ?>
            <div class="tfa-code-wrapper">
            <? for ($i = 0; $i < 6; $i += 1): ?>
                <input required type="number" name="tfacode-input[<?= $i ?>]" value=""
                       max="9" pattern="^\d$" maxlength="1" class="no-hint"
                       <? if ($i === 0) echo 'autofocus'; ?>>
            <? endfor; ?>
            </div>
<?php
    // We need to strip all whitespace between html nodes so that they have no
    // visual gap between them.
    $__content = ob_get_clean();
    $__content = trim($__content);
    $__content = preg_replace('/>\s+</', '><', $__content);
    print $__content;
?>
        </div>

    <? if ($global): ?>
        <label>
            <input type="checkbox" name="tfa-trusted" value="1">
            <?= _('Diesem Gerät für 30 Tage vertrauen.') ?>
        </label>
    <? endif; ?>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Prüfen')) ?>
    <? if ($global && !$secret->confirmed): ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            URLHelper::getURL('dispatch.php/tfa/abort')
        ) ?>
    <? endif; ?>
    </footer>
<? endif; ?>
</form>
