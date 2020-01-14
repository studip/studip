<? if ($show_form): ?>
    <form class="default" method="post" data-dialog="reload-on-close"
          action="<?= $controller->link_for(
                  'resources/room_request/delete/' . $request->id
                  )?>">
        <?= MessageBox::info(
            _('Soll die folgende Anfrage wirklich gelöscht werden?')
        ) ?>
        <?= CSRFProtection::tokenTag() ?>
        <?= $this->render_partial('resources/_common/_request_info.php') ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Löschen'), 'delete') ?>
        </div>
    </form>
<? endif ?>
