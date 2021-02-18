<? if ($requests): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('room_request/assign') ?>">
        <table class="default sortable-table request-list" data-sortlist="[[8, 0]]">
            <caption>
                <?= sprintf(
                    ngettext(
                        'Anfragenliste (%d Anfrage)',
                        'Anfragenliste (%d Anfragen)',
                        $count_requests
                    ),
                    $count_requests
                ) ?>
            </caption>
            <thead>
                <tr>
                    <th data-sort="htmldata">
                        <?= Icon::create('radiobutton-checked')->asImg(
                            [
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
                    <? if ($request->getTimeIntervals()) : ?>
                        <?= $this->render_partial(
                            'resources/_common/_request_tr',
                            ['request' => $request]
                        ) ?>
                    <? endif ?>
                <? endforeach ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="11">
                    <section style="float: right">
                        <?= $pagination->asLinks(function ($page) use ($controller) {
                            return $controller->url_for("resources/room_request/overview/{$page}");
                        }) ?>
                    </section>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
<? else: ?>
    <?= MessageBox::info(_('Es sind keine Anfragen vorhanden!')) ?>
<? endif ?>
