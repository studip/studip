<form action="<?= $controller->link_for("admin/configuration/edit_{$range_type}_config", $range, compact('field')) ?>"
      method="post" data-dialo="size=auto" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= htmlReady($field) ?>
        </legend>
        <?= $this->render_partial('admin/configuration/type-edit.php', $config) ?>
        <label>
            <?= _('Beschreibung:') ?> (<em>description</em>)
            <textarea name="description" readonly><?= htmlReady($config['description']) ?></textarea>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("admin/configuration/{$range_type}_configuration", ['id' => $range->id])
        ) ?>
    </footer>
</form>
