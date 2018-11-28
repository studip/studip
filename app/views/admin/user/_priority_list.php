<section class="contentbox">
    <header>
        <h1>
            <a href="<?= ContentBoxHelper::href('priorities') ?>">
                <?= _('Übersicht Anmeldelisten von Veranstaltungen mit automatischer Platzvergabe') ?>
            </a>
        </h1>
    </header>
    <? if (!empty($priorities)) : ?>
        <section>
            <table class="default">
                <colgroup>
                    <col style="width: 200px">
                    <col>
                    <col style="width: 15%">
                </colgroup>
                <thead>
                    <tr>
                        <th><?= _('Veranstaltungsnummer') ?></th>
                        <th><?= _('Veranstaltung') ?></th>
                        <th style="text-align: center"><?= _('Priorität') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($priorities as $priority): ?>
                        <? $course = Course::find($priority['seminar_id']) ?>
                        <tr>
                            <td>
                                <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]) ?>">
                                    <?= htmlReady($course->veranstaltungsnummer) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]) ?>">
                                    <?= sprintf('%s (%s)', htmlReady($course->getFullName('type-name')), htmlReady($course->getFullName('sem-duration-name'))) ?>
                                </a>
                            </td>
                            <td style="text-align: center"><?= htmlReady($priority['priority']) ?></td>
                        </tr>
                    <? endforeach; ?>
                </tbody>
            </table>
        </section>
    <? else : ?>
        <?= $this->render_partial('admin/user/_activities_no_courses.php') ?>
    <? endif ?>
</section>