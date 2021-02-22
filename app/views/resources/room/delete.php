<? if ($show_form): ?>
<form class="default" method="post" data-dialog="reload-on-close"
    action="<?= $controller->link_for('resources/room/delete/' . $room->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <h1><?= _('Soll der folgende Raum wirklich gelöscht werden?') ?></h1>
    <?= $this->render_partial('resources/room/index.php', [
        'room' => $room,
        'grouped_properties' => $room->getGroupedProperties()
    ]) ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Löschen'), 'confirmed') ?>
    </div>
</form>
<? endif ?>
