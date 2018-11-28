<section class="contentbox">
    <header>
        <h1><?= $headline ?></h1>
    </header>
    <? if (!empty($memberships)) : ?>
        <? foreach ($memberships as $semester_name => $_memberships) : ?>
            <article id="<?= $semester_name ?>" class="<?= ContentBoxHelper::classes($semester_name) ?>">
                <header>
                    <h1>
                        <a href="<?= ContentBoxHelper::href($semester_name) ?>">
                            <?= $semester_name ?>
                        </a>
                    </h1>
                </header>
                <section>
                    <table class="default">
                        <colgroup>
                            <col style="width: 200px">
                            <col>
                            <col style="width: 15%">
                            <col style="width: 15%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th><?= _('Veranstaltungsnummer') ?></th>
                                <th><?= _('Veranstaltung') ?></th>
                                <th><?= _('Typ') ?></th>
                                <th><?= _('Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <? foreach ($_memberships as $membership): ?>
                                <tr>
                                    <td>
                                        <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $membership->course->id]) ?>">
                                            <?= htmlReady($membership->course->veranstaltungsnummer) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $membership->course->id]) ?>">
                                            <?= htmlReady($membership->course->name) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= htmlReady($membership->course->getSemType()['name'])?>
                                    </td>
                                    <td>
                                        <?= htmlReady($membership->status) ?>
                                    </td>
                                </tr>
                            <? endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </article>
        <? endforeach ?>
    <? else : ?>
        <?= $this->render_partial('admin/user/_activities_no_courses.php') ?>
    <? endif ?>
</section>
