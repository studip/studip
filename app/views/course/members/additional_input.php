<form class="default" method="post">
    <fieldset>
        <legend>
            <?= htmlReady($aux->name) ?>
        </legend>

        <p><?= formatReady($aux->description) ?></p>

        <? foreach ($datafields as $field): ?>
            <? if ($field->getTypedDatafield()->isVisible()): ?>
                <? if ($field->getTypedDatafield()->isEditable()) : ?>
                    <? $editable = true; ?>
                <? endif ?>
                <?= $field->getTypedDatafield()->getHTML('aux'); ?>
            <? endif; ?>
        <? endforeach; ?>
    </fieldset>

    <? if ($editable): ?>
    <footer>
        <?= \Studip\Button::create(_('Speichern'), 'save') ?>
    </footer>
    <? else: ?>
        <?= MessageBox::info(_('Keine einstellbaren Zusatzdaten vorhanden')) ?>
    <? endif; ?>
</form>
