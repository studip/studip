<? if ($show_data): ?>
    <form class="default"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/global_locks/delete/' . $lock->id
                  ) ?>"
          method="post" data-dialog="reload-on-close">
        <?= CSRFProtection::tokenTag() ?>
        <h2><?= _('Soll die folgende Sperrung wirklich gelöscht werden?') ?></h2>
        <dl>
            <dt><?= _('Startzeitpunkt') ?></dt>
            <dd><?= date('d.m.Y H:i', $lock->begin) ?></dd>
            <dt><?= _('Endzeitpunkt') ?></dt>
            <dd><?= date('d.m.Y H:i', $lock->end) ?></dd>
            <dt><?= _('Typ der Sperrung') ?></dt>
            <dd><?= htmlReady($lock->getTypeString()) ?></dd>
        </dl>
        <div data-dialog-button>
            <?= \Studip\Button::create(
                _('Löschen'),
                'delete'
            ) ?>
        </div>
    </form>
<? endif ?>
