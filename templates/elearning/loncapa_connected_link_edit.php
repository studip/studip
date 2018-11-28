<form method="post" action="<?= URLHelper::getLink() ?>">
    <input type="hidden" name="view" value="<?= htmlReady($view) ?>">
    <input type="hidden" name="search_key" value="<?= htmlReady($search_key) ?>">
    <input type="hidden" name="cms_select" value="<?= htmlReady($cms_select) ?>">
    <input type="hidden" name="module_type" value="loncapa">
    <input type="hidden" name="module_id" value="<?= htmlReady($current_module) ?>">
    <input type="hidden" name="module_system_type" value="<?= htmlReady($cms_type) ?>">

    <? if ($connected) : ?>
        <?= Studip\Button::create(_('Entfernen'), 'remove') ?>
    <? else : ?>
        <?= Studip\Button::create(_('Hinzufügen'), 'add') ?>
    <? endif ?>
</form>
