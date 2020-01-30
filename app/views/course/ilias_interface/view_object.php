<form class="default" action="<?= $controller->url_for($module->getRoute('view_course')) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
	<input type="hidden" name="ilias_search" value="<?=htmlReady($ilias_search)?>">
	<input type="hidden" name="ilias_module_id" value="<?=htmlReady($module_id)?>">
	<input type="hidden" name="ilias_ref_id" value="<?=htmlReady($module_id)?>">
    <?= $this->render_partial('my_ilias_accounts/_ilias_module.php') ?>
    <footer data-dialog-button>
        <? if ($ilias->isActive() && $mode && $edit_permission) : ?>
            <?= Studip\LinkButton::create(_('Zurück'), $controller->url_for('course/ilias_interface/add_object/'.$mode.'/'.$ilias_index.'?ilias_search=' . $ilias_search), $dialog ? ['data-dialog' => 'size=auto'] : []) ?>
            <?= Studip\LinkButton::create(_('Hinzufügen'), $controller->url_for($module->getRoute('add') .'?ilias_search=' . $ilias_search), $dialog ? ['data-dialog' => ''] : []) ?>
        <? endif ?>
        <? if ($ilias->isActive() && !$mode) : ?>
            <? if ($edit_permission && $module->isAllowed('delete')) : ?>
                <?= Studip\LinkButton::create(_('Entfernen'), $controller->url_for($module->getRoute('remove') . '?ilias_search=' . $ilias_search), ['data-confirm' => $module->siblings_count < 2 ? sprintf(_('Dies ist die einzige Instanz des Objekts "%s". Durch das Entfernen aus dem Kurs wird das Objekt unwiderruflich gelöscht! Wollen Sie das Objekt wirklich löschen?'), htmlReady($module->getTitle())) : sprintf(_('Wollen Sie das Objekt "%s" jetzt entfernen?'), htmlReady($module->getTitle()))]) ?>
            <? endif ?>
            <?= $module->isAllowed('start') ? Studip\LinkButton::create(_('Starten'), $controller->url_for($module->getRoute('start')), ['target' => '_blank', 'rel' => 'noopener noreferrer']) :'' ?>
            <?= $module->isAllowed('edit') ? Studip\LinkButton::create(_('Bearbeiten'), $controller->url_for($module->getRoute('edit')), ['target' => '_blank', 'rel' => 'noopener noreferrer']) :'' ?>
        <? endif ?>
        <? if ($dialog) : ?>
        <?= Studip\Button::createCancel(_('Schließen'), 'cancel', ['data-dialog' => 'close']) ?>
        <? endif ?>
    </footer>
</form>
