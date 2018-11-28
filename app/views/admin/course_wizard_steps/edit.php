<form class="default" action="<?= $controller->url_for('admin/coursewizardsteps/save', $step->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= PageLayout::getTitle() ?></legend>

        <label>
            <span class="required"><?= _('Name des Schrittes') ?></span>
            <input type="text" name="name" size="50" maxlength="255" value="<?= htmlReady($step->name) ?>" required>
        </label>
        <label for="classname">
            <span class="required"><?= _('PHP-Klasse') ?></span>
            <input type="text" name="classname" size="50" maxlength="255" value="<?=
            htmlReady($step->classname) ?>" required>
        </label>
        <? if ($availableClasses && count($availableClasses)) : ?>
            <div>
                <ul class="clean">
                    <? foreach ($availableClasses as $className) : ?>
                        <li>
                            <a href="#" onClick="jQuery('input[name=classname]').val('<?= htmlReady($className) ?>');">
                                <?= Icon::create('arr_2up', 'info')->asImg(['class' => "text-bottom"]) ?>
                                <?= htmlReady($className) ?>
                            </a>
                        </li>
                    <? endforeach ?>
                </ul>
            </div>
        <? endif ?>
        <label>
            <span class="required">  <?= _('Nummer des Schritts im Assistenten') ?></span>
            <input type="number" name="number" size="4" maxlength="2" value="<?= $step->number ?>" required>
        </label>
        <label>
            <input type="checkbox" name="enabled"<?= $step->enabled ? ' checked="checked"' : '' ?>>
            <?= _('Schritt ist aktiv') ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
