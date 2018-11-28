<section class="contentbox">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href('institutes') ?>">
                <?= _('Dateiübersicht Einrichtungen') ?>
            </a>
        </h1>
    </header>
    <section>
        <table class="default">
            <colgroup>
                <col>
                <col style="width: 120px">
                <col style="width: 20px">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Dateiname') ?></th>
                    <th><?= _('Anzahl') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($institutes as $institute): ?>
                    <tr>
                        <td>
                            <?= htmlReady($institute['Name']) ?>
                        </td>
                        <td>
                            <? if ((int)$institute['files']) : ?>
                                <?= sprintf('%u %s', $institute['files'], _('Dokumente')) ?>
                            <? else : ?>
                                -
                            <? endif ?>
                        </td>
                        <td class="actions">
                            <? if ($institute['files']) : ?>
                                <?
                                $actionMenu = ActionMenu::get();
                                $actionMenu->addLink($controller->url_for('admin/user/list_files/' . $user['user_id'] . '/' . $institute['Institut_id'] , $params),
                                    _('Dateien auflisten'),
                                    Icon::create('folder-full', 'clickable'),
                                    ['data-dialog' => 'size=50%']);
                                $actionMenu->addLink($controller->url_for('admin/user/download_user_files/' . $user['user_id'] . '/' . $institute['Institut_id']),
                                    _('Dateien als ZIP herunterladen'),
                                    Icon::create('download', 'clickable'));

                                ?>
                                <?= $actionMenu->render() ?>
                            <? endif ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </section>
</section>

