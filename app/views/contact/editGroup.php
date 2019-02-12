<form class="default" method="post" action="<?= $controller->link_for('contact/editGroup/' . $group->id) ?>'">
    <? CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend class="hide-in-dialog">
            <? if ($group->isNew()) : ?>
                <?= _('Gruppe anlegen') ?>
            <? else : ?>
                <?= _('Gruppe bearbeiten') ?>
            <? endif ?>
        </legend>
        <label>
            <span class="required"><?= _('Gruppenname') ?></span>
            <input required type="text" name="name"
                   placeholder="<?= _('Gruppenname') ?>"
                   value='<?= htmlReady($group->name) ?>'>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept($group->isNew() ? _('Anlegen') : _('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('contact/index')
        ) ?>
    </footer>
</form>
