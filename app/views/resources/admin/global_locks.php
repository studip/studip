<? if ($current_lock): ?>
    <?= MessageBox::info(
        sprintf(
            _('Die Raum- und Ressourcenverwaltung ist zurzeit gesperrt. Die Sperre besteht vom %1$s bis zum %2$s.'),
            date(_('d.m.Y H:i'), $current_lock->begin),
            date(_('d.m.Y H:i'), $current_lock->end)
        )
    ) ?>
<? endif ?>
<? if ($future_locks): ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Beginn') ?></th>
                <th><?= _('Ende') ?></th>
                <th><?= _('Typ der Sperrung') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($future_locks as $lock): ?>
                <tr>
                    <td><?= date(_('d.m.Y H:i'), $lock->begin) ?></td>
                    <td><?= date(_('d.m.Y H:i'), $lock->end) ?></td>
                    <td><?= $lock->getTypeString() ?></td>
                    <td class="actions">
                        <?= ActionMenu::get()
                            ->addLink(
                                $controller->url_for('resources/global_locks/delete/' . $lock->id),
                                _('Sperrung bearbeiten'),
                                Icon::create('edit'),
                                [
                                    'data-dialog' => 'size=auto'
                                ])
                            ->addLink(
                                $controller->url_for('resources/global_locks/delete/' . $lock->id),
                                _('Sperrung löschen'),
                                Icon::create('trash'),
                                [
                                    'data-dialog' => 'size=auto'
                                ]
                            )
                            ->render();
                        ?>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <?= MessageBox::info(
        _('Es sind keine gegenwärtigen und zukünftigen Sperren der Raum- und Resourcenverwaltung hinterlegt.')
    ) ?>
<? endif ?>
