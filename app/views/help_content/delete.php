<? use Studip\Button, Studip\LinkButton; ?>
<form id="delete_help_content_form" class="default"
      action="<?= $controller->url_for('help_content/delete/' . $help_content_id) ?>"
      method="POST">
    <?=CSRFProtection::tokenTag(); ?>
    <fieldset>
        <input type="hidden" name="help_content_route" value="<?=$help_content->route?>">
        <legend><?= sprintf(_('Seite %s'), $help_content->route) ?></legend>
        <?= _('Hilfe-Text:') ?>
        <?= $help_content->content ? htmlReady($help_content->content) : '' ?>
    </fieldset>

    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <? if ($via_ajax): ?>
            <?= Button::create(_('Löschen'), 'delete_help_content', ['data-dialog' => '']) ?>
        <? else: ?>
            <?= Button::create(_('Löschen'), 'delete_help_content') ?>
        <? endif; ?>
    </footer>
</form>
