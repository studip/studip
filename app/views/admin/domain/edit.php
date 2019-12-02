<form action="<?= $controller->link_for("admin/domain/save/{$domain->id}") ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend class="hide-in-dialog">
        <? if ($domain->isNew()): ?>
            <?= _('Neue Nutzerdomäne anlegen') ?>
        <? else: ?>
            <?= _('Nutzerdomäne bearbeiten') ?>
        <? endif; ?>
        </legend>

        <label>
            <span class="required"><?= _('ID der Domäne') ?></span>
            <input required type="text" name="id" pattern="<?= UserDomain::REGEXP ?>"
                   maxlength="32" value="<?= htmlReady($domain->id) ?>">
        </label>

        <label>
            <span class="required"><?= _('Name der Domäne') ?></span>
            <input required type="text" name="name"
                   value="<?= htmlReady($domain->name) ?>">
        </label>

        <label>
            <?= _('Sichtbarkeit innerhalb der Domäne') ?>
        </label>

        <label class="undecorated" style="display: block;">
            <input type="radio" name="unrestricted" value="0"
                   <? if ($domain->restricted_access) echo 'checked'; ?>>
            <?= _('Nutzer bleiben innerhalb der Domäne und können keine Nutzer ausserhalb sehen') ?>
        </label>

        <label class="undecorated" style="display: block;">
            <input type="radio" name="unrestricted" value="1"
                   <? if (!$domain->restricted_access) echo 'checked'; ?>>
            <?= _('Nutzer der Domäne können das System uneingeschränkt nutzen') ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'), $controller->url_for('admin/domain')
        ) ?>
    </footer>
</form>
