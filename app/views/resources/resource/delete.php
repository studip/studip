<? if ($show_form): ?>
<form class="default" method="post" data-dialog="reload-on-close"
    action="<?= URLHelper::getLink('dispatch.php/room_management/resource/delete/' . $resource->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <h1><?= _('Soll die folgende Ressource wirklich gelöscht werden?') ?></h1>
    <?= $this->render_partial('resources/resource/index.php', ['resource' => $resource]) ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Löschen'), 'confirmed') ?>
    </div>
</form>
<? endif ?>
