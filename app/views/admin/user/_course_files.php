<section class="contentbox">
    <header>
        <h1>
            <?= _('Dateiübersicht Veranstaltungen') ?>
        </h1>
    </header>
    <? foreach ($course_files as $semester_name => $file_date) : ?>
        <article id="<?= $semester_name ?>" class="<?= ContentBoxHelper::classes($semester_name) ?>">
            <header>
                <h1>
                    <a href="<?= ContentBoxHelper::href($semester_name) ?>">
                        <?= htmlReady($semester_name) ?>
                    </a>
                </h1>
            </header>
            <section>
                <table class="default">
                    <colgroup>
                        <col style="width: 200px">
                        <col>
                        <col style="width: 120px">
                        <col style="width: 120px">
                        <col style="width: 20px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><?= _('Veranstaltungsnummer') ?></th>
                            <th><?= _('Veranstaltung') ?></th>
                            <th><?= _('Typ') ?></th>
                            <th><?= _('Anzahl') ?></th>
                            <th class="actions"><?= _('Aktionen') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <? foreach ($file_date as $data): ?>
                            <tr>
                                <td><?= htmlReady($data['course']->veranstaltungsnummer) ?></td>
                                <td>
                                    <?= htmlReady($data['course']->name) ?>
                                </td>
                                <td>
                                    <?= htmlReady($data['course']->getSemType()['name'])?>
                                </td>
                                <td>
                                    <? if ($data['files']) : ?>
                                        <?= sprintf('%u %s', $data['files'], _('Dokumente')) ?>
                                    <? else : ?>
                                        -
                                    <? endif ?>
                                </td>
                                <td class="actions">
                                    <? if ($data['files']) : ?>
                                        <?
                                        $actionMenu = ActionMenu::get();
                                        $actionMenu->addLink($controller->url_for('admin/user/list_files/' . $user['user_id'] . '/' . $data['course']->id, $params),
                                                _('Dateien auflisten'),
                                                Icon::create('folder-full', 'clickable'),
                                                ['data-dialog' => 'size=50%']);
                                        $actionMenu->addLink($controller->url_for('admin/user/download_user_files/' . $user['user_id'] . '/' . $data['course']->id),
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
        </article>
    <? endforeach; ?>
</section>