<? if (!count($categories)) { ?>
    <?= \MessageBox::info(_('Es wurden noch keine Leistungen definiert.')) ?>
<? } else { ?>

    <form class="default gradebook-lecturer-weights" action="<?= $controller->url_for('course/gradebook/lecturers/store_weights') ?>" method="POST">

        <? foreach ($categories as $category) { ?>
            <fieldset>

                <legend><?= htmlReady($category) ?></legend>

                <? foreach ($groupedDefinitions[$category] as $definition) { ?>
                    <label>
                        <?= htmlReady($definition->name) ?>
                        <input type="number" name="definitions[<?= $definition->id ?>]" value="<?= htmlReady($definition->weight) ?>" min="0" max="1000000">
                    </label>
                <? } ?>

            </fieldset>
        <? } ?>

        <footer>
            <?= \Studip\Button::createAccept(_('Speichern')) ?>
            <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/gradebook/lecturers')) ?>
        </footer>
    </form>

<? } ?>
