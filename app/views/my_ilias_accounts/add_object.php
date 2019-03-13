<form class="default" action="<?= $controller->url_for('my_ilias_accounts/redirect/'.$ilias_index.'/new/'.$ilias_ref_id) ?>" method="post" target="_blank">
    <?= CSRFProtection::tokenTag() ?>
	<input type="hidden" name="ilias_target" value="new">
	<input type="hidden" name="ilias_ref_id" value="<?=$ilias_ref_id?>">
    <label>
        <span class="required"><?= _('Art des Lernobjekts') ?></span>
        <select name="ilias_module_type" required>
        <option></option>
        <? foreach ($ilias->getAllowedModuleTypes() as $module_index => $module_name) : ?>
            <option value="<?=$module_index?>"><?=$module_name?></option>
        <? endforeach ?>
        </select>
    </label>
    <footer data-dialog-button>
        <? if ($ilias->isActive()) : ?>
        <?= Studip\Button::createAccept(_('Erstellen'), 'submit') ?>
        <? endif ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
<script>
jQuery(function ($) {
    $('button[name=submit]').click(function () {
        window.location.reload();
    });
});
</script>