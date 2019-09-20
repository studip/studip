<form class="default" action="<?= $controller->url_for('course/ilias_interface/add_groups/'.$ilias_index) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <? if (!$ilias_index) : ?>
    <label>
        <span class="required"><?= _('ILIAS-Installation auswählen') ?></span>
        <select name="ilias_index" required>
        <option></option>
        <? foreach ($ilias_list as $ilias_list_index => $ilias) : ?>
            <option value="<?=$ilias_list_index?>"><?=$ilias->getName()?></option>
        <? endforeach ?>
        </select>
    </label>
    <? elseif ($mode == 'add_groups') : ?>
    <? else : ?>
    <div>
        <input type="hidden" name="cmd" value="create_groups">
        <h3><?= _('Gruppen') ?></h3>
        <? foreach ($groups as $group) : ?>
        <article>
            <?=$group->getName()?> (<?=count($group->members)?>)
        </article>
        <? endforeach ?>
        <?= $groups_exist ? sprintf(_('Die Gruppen können nun in der %s-Installation aktualisiert werden.'), $ilias->getName()) : sprintf(_('Die Gruppen können nun in der %s-Installation angelegt werden.'), $ilias->getName())?>
    </div>
    <? endif ?>
    <footer data-dialog-button>
        <? if ($ilias->isActive() && $submit_text) : ?>
        <?= Studip\Button::create($submit_text, 'submit', ($dialog && ! $ilias_index) ? ['data-dialog' => 'size=auto'] : []) ?>
        <? endif ?>
        <?= Studip\Button::createCancel(_('Schließen'), 'cancel', $dialog ? ['data-dialog' => 'close'] : []) ?>
    </footer>
</form>