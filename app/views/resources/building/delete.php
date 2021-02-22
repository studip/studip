<? if ($show_form): ?>
    <?= MessageBox::warning(
        _('Soll das folgende Gebäude wirklich gelöscht werden?')
    ) ?>
    <form class="default" method="post" data-dialog="reload-on-close"
          action="<?= $controller->link_for('resources/building/delete/' . $building->id) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <?= $this->render_partial('resources/building/index', ['building' => $building]) ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Löschen'), 'save') ?>
        </div>
    </form>
<? endif ?>
