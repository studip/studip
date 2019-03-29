<form action="<?= $controller->url_for('admin/datafields/config/' . $struct->getID()) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <input type="hidden" name="typeparam" value="<?= htmlReady(rtrim($struct->getTypeParam()), false) ?>">

    <fieldset>
        <legend><?= _('Vorschau') ?></legend>

        <?= $preview->getHTML('dummy') ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\Button::create(_('Bearbeiten'), 'edit', ['data-dialog' => 'size=auto']) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/datafields/index/' . $struct->getObjectType() . '#' . $struct->getObjectType())) ?>
    </footer>
</form>
