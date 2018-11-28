<form class="default settings-avatar" enctype="multipart/form-data"
        action="<?= $controller->url_for('avatar/upload', $type, $id) ?>" method="post">
    <fieldset>
        <legend>
            <?= $type == 'user' ? _('Profilbild bearbeiten und zuschneiden') :
                ($type == 'course' ? _('Veranstaltungsbild bearbeiten und zuschneiden') :
                    _('Einrichtungsbild bearbeiten und zuschneiden')) ?>
        </legend>
        <div class="form-group">
            <div id="avatar-preview">
                <img class="avatar-normal" id="new-avatar" src="<?= $avatar ?>"
                     data-message-too-small="<?= _('Das Bild ist kleiner als 250 x 250 Pixel. Wollen Sie wirklich fortfahren?') ?>">
            </div>

            <label class="file-upload">
                <?= _('Wählen Sie ein Bild von Ihrer Festplatte aus.') ?>
                <input type="file" id="avatar-upload" accept="image/gif,image/png,image/jpeg"
                       capture="camera"
                       data-max-size="<?= Avatar::MAX_FILE_SIZE ?>"
                       data-message-too-large="<?= _('Die hochgeladene Datei ist zu groß. Bitte wählen Sie ein anderes Bild.') ?>">

                <p class="form-text">
                    <?= sprintf(
                        _('Die Bilddatei darf max. %s groß sein, es sind nur Dateien mit den Endungen .jpg, .png oder .gif erlaubt!'),
                        relsize(Avatar::MAX_FILE_SIZE)
                    ) ?>
                </p>

                <a onclick="javascript:void 0" class=button>Auswählen</a>
            </label>

            <input type="hidden" name="cropped-image" id="cropped-image" value="">

            <div id="avatar-buttons" class="hidden-js">
                <a href="" id="avatar-zoom-in" title="<?= _('Vergrößern') ?>">
                    <?= Icon::create('zoom-in')->asImg(24) ?>
                    <?= _('Vergrößern') ?>
                </a>
                <a href="" id="avatar-zoom-out" title="<?= _('Verkleinern') ?>">
                    <?= Icon::create('zoom-out')->asImg(24) ?>
                    <?= _('Verkleinern') ?>
                </a>
                <a href="" id="avatar-rotate-clockwise" title="<?= _('Nach rechts drehen') ?>">
                    <?= Icon::create('rotate-right')->asImg(24) ?>
                    <?= _('Nach rechts drehen') ?>
                </a>
                <a href="" id="avatar-rotate-counter-clockwise" title="<?= _('Nach links drehen') ?>">
                    <?= Icon::create('rotate-left')->asImg(24) ?>
                    <?= _('Nach links drehen') ?>
                </a>
            </div>
        </div>
        <?= CSRFProtection::tokenTag() ?>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Absenden'), 'upload', ['id' => 'submit-avatar']) ?>
        <? if ($customized): ?>
            <?= Studip\LinkButton::create(
                _('Aktuelles Bild löschen'),
                $controller->url_for('avatar/delete', $type, $id)
            ) ?>
        <? endif ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $cancel_link) ?>
    </footer>
</form>
