<? if ($show_form): ?>
    <?= MessageBox::warning(
        _('Soll der folgende Standort wirklich gelöscht werden?')
    ) ?>
    <form class="default" method="post" data-dialog="reload-on-close"
          action="<?= $controller->link_for('resources/location/delete/' . $location->id) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <?= $this->render_partial('resources/location/index', ['location' => $location]) ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Löschen'), 'save') ?>
        </div>
    </form>
<? endif ?>
