<? if (!count($categories)) { ?>
    <?= \MessageBox::info(_('Es wurden noch keine Leistungen definiert.')) ?>
<? } else { ?>

    <form class="default gradebook-lecturer-weights" action="<?= $controller->link_for('course/gradebook/lecturers/store_weights') ?>" method="POST">
        <?= CSRFProtection::tokenTag()?>
        <span class="content-title"><?= _('Gewichtungen') ?></span>

        <? foreach ($categories as $category) { ?>
            <fieldset>

                <legend><?= $controller->formatCategory($category) ?></legend>

                <? foreach ($groupedDefinitions[$category] as $definition) { ?>
                    <?= $this->render_partial('course/gradebook/lecturers/_weight', compact('definition')) ?>
                <? } ?>

            </fieldset>
        <? } ?>

        <footer>
            <?= \Studip\Button::createAccept(_('Speichern')) ?>
            <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/gradebook/lecturers')) ?>
        </footer>
    </form>

<? } ?>