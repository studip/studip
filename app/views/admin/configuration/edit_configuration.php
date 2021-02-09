<form action="<?= $controller->link_for('admin/configuration/edit_configuration', ['field' => $config['field']]) ?>"
      method="post" data-dialog="size=auto" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Konfigurationsparameter editieren') ?>
        </legend>

        <? if ($config['description']): ?>
            <p><?= htmlReady($config['description']) ?></p>
        <? endif ?>

        <?= $this->render_partial('admin/configuration/type-edit.php', $config) ?>

        <label for="comment">
            <?= _('Kommentar') ?>
            <textarea cols="80" rows="2" name="comment" id="comment"><?= htmlReady($config['comment']) ?></textarea>
        </label>
        <label>
            <?= _('Standard') ?>

            <? if ($config['is_default'] === '1'): ?>
                <?= Icon::create('checkbox-checked', Icon::ROLE_INFO)->asImg(['title' => _('Ja')]) ?>
            <? elseif ($config['is_default'] === '0'): ?>
                <?= Icon::create('checkbox-unchecked', Icon::ROLE_INFO)->asImg(['title' => _('Nein')]) ?>
            <? elseif ($config['is_default'] === null): ?>
                <em>- <?= _('kein Eintrag vorhanden') ?> -</em>
            <? endif ?>
        </label>
        <div>
            <label class="col-3">
                <?= _('Typ') ?>
                <input name="type" type="text" readonly value="<?= htmlReady($config['type']) ?>">
            </label>
            <label class="col-3">
                <?= _('Bereich') ?>
                <input type="text" name="range" readonly value="<?= htmlReady($config['range']) ?>">
            </label>
        </div>
        <div>
            <label class="col-3">
                <?= _('Kategorie') ?>
                <select name="section" onchange="jQuery(this).next('input').val( jQuery(this).val() );">
                    <? foreach (array_keys($allconfigs) as $section): ?>
                        <option <? if ($config['section'] === $section) echo 'selected'; ?>>
                            <?= htmlReady($section) ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </label>
            <label class="col-3">
                (<em><?= _('Bitte die neue Kategorie eingeben') ?></em>)
                <input type="text" name="section_new" id="section">
            </label>
        </div>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Übernehmen')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("admin/configuration/configuration/{$config['section']}")
        ) ?>
    </footer>
</form>
