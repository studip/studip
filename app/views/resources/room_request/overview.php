<? if ($requests): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('room_request/assign') ?>">
        <table class="default sortable-table request-list" data-sortlist="[[8, 0]]">
            <thead>
                <tr>
                    <th data-sort="htmldata">
                        <?= Icon::create('radiobutton-checked')->asImg(
                            [
                                'class' => 'text-bottom',
                                'title' => _('Markierung')
                            ]
                        ) ?>
                    </th>
                    <th data-sort="text"><?= _('Nr.') ?></th>
                    <th data-sort="text"><?= _('Name') ?></th>
                    <th data-sort="text"><?= _('Lehrende Person(en)') ?></th>
                    <th data-sort="text"><?= _('Raum') ?></th>
                    <th data-sort="text"><?= _('Plätze') ?></th>
                    <th data-sort="text"><?= _('Anfragende Person') ?></th>
                    <th data-sort="htmldata"><?= _('Art') ?></th>
                    <th data-sort="htmldata"><?= _('Dringlichkeit') ?></th>
                    <th data-sort="num"><?= _('letzte Änderung') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($requests as $request): ?>
                    <?= $this->render_partial(
                        'resources/_common/_request_tr',
                        ['request' => $request]
                    ) ?>
                <? endforeach ?>
            </tbody>
        </table>
    </form>
<? else: ?>
    <?= MessageBox::info(_('Es sind keine Anfragen vorhanden!')) ?>
<? endif ?>
