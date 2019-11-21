<form class="default" method="get" action="<?= $controller->link_for('search/archive') ?>">
    <input type="hidden" name="my_courses_only" value="<?= (int) $my_courses_only ?>">

    <fieldset>
        <legend>
            <?= _('Suche im Veranstaltungsarchiv') ?>
        </legend>

        <label class="col-3">
            <?= _('Name der Veranstaltung') ?>
            <input autofocus type="text" name="criteria"
                   minlength="<?= $controller::NEEDLE_MIN_LENGTH ?>"
                   placeholder="<?= _('Veranstaltung suchen') ?>"
                   value="<?= htmlReady($criteria) ?>">
        </label>

        <label class="col-3">
            <?= _('Name des/der Lehrenden') ?>
            <input type="text" name="teacher"
                   minlength="<?= $controller::NEEDLE_MIN_LENGTH ?>"
                   placeholder="<?= _('Lehrende suchen') ?>"
                   value="<?= htmlReady($teacher) ?>">
        </label>

        <label class="col-3">
            <?= _('Semester') ?>
            <select name="semester">
                <option value=""><?= _('alle') ?></option>
            <? foreach ($semesters as $one) : ?>
                <option value="<?= htmlReady($one) ?>" <? if ($semester === $one) echo 'selected'; ?>>
                    <?= htmlReady($one) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>

        <label class="col-3">
            <?= _('Einrichtung') ?>
            <select name="institute">
                <option value=""><?= _('alle') ?></option>
            <? foreach ($institutes as $one) : ?>
                <option value="<?= htmlReady($one) ?>" <? if ($institute === $one) echo 'selected'; ?>>
                    <?= htmlReady($one) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
    </fieldset>
    <footer>
        <?= Studip\Button::create(_('Suchen')) ?>
        <?= Studip\LinkButton::create(
            _('Zurücksetzen'),
            $controller->url_for('search/archive')
        ) ?>
    </footer>
</form>

<? if ($courses) : ?>
    <br>
    <form action="" method="post">
        <table class="default withdetails">
            <colgroup>
                <col>
                <col width="20%">
                <col width="20%">
                <col width="20%">
                <col width="120px">
            </colgroup>
            <thead>
                <tr>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Lehrende') ?></th>
                    <th><?= _('Einrichtungen') ?></th>
                    <th><?= _('Semester') ?></th>
                    <th><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
            <? foreach ($courses as $course) : ?>
                <tr <? if (count($courses) === 1) echo 'class="open"'; ?>>
                    <td onclick="jQuery(this).closest('tr').toggleClass('open'); return false;">
                        <?= htmlReady($course->name) ?>
                    </td>
                    <td><?= htmlReady($course->dozenten) ?></td>
                    <td><?= htmlReady($course->institute) ?></td>
                    <td><?= htmlReady($course->semester) ?></td>
                    <td class="actions">
                    <? if (archiv_check_perm($course->id)) : ?>
                        <a href="<?= $controller->link_for("archive/overview/{$course->id}") ?>" data-dialog>
                            <?= Icon::create('info-circle') ?>
                        </a>
                    <? endif ?>

                    <? if ($course->archiv_file_id && archiv_check_perm($course->id)): ?>
                        <a href="<?= FileManager::getDownloadLinkForArchivedCourse($course, false) ?>">
                            <?= Icon::create('file-archive') ?>
                        </a>
                    <? endif ?>
                    <? if ($course->archiv_protected_file_id && in_array(archiv_check_perm($course->id), ['tutor', 'dozent', 'admin'])): ?>
                        <a href="<?= FileManager::getDownloadLinkForArchivedCourse($course, true) ?>">
                            <?= Icon::create('file-archive') ?>
                        </a>
                    <? endif ?>
                    <? if (archiv_check_perm($course->id)): ?>
                        <a href="<?= $controller->link_for("archive/forum/{$course->id}") ?>" data-dialog>
                            <?= Icon::create('forum') ?>
                        </a>
                        <a href="<?= $controller->link_for("archive/wiki/{$course->id}") ?>" data-dialog>
                            <?= Icon::create('wiki') ?>
                        </a>
                    <? endif ?>
                    <? if (archiv_check_perm($course->id) === 'admin'): ?>
                        <?= Icon::create('trash')->asInput(tooltip2(_('Löschen')) + [
                            'formaction' => URLHelper::getURL(
                                "dispatch.php/archive/delete/{$course->id}",
                                compact('criteria', 'teacher', 'semester', 'institute', 'my_courses_only')
                            ),
                            'data-confirm' => sprintf(
                                _('Soll die Veranstaltung %1$s wirklich aus dem Archiv gelöscht werden?'),
                                htmlReady($course->name)
                            ),
                            'class' => 'text-top',
                        ]) ?>
                    <? endif ?>
                    </td>
                </tr>
                <tr class="details nohover">
                    <td colspan="5" class="detailscontainer">
                        <ul class="default nohover">
                            <li>
                                <strong><?= _('Fakultät') ?>:</strong>
                                <?= htmlReady($course->fakultaet) ?>
                            </li>
                            <li>
                                <strong><?= _('Bereich') ?>:</strong>
                                <?= htmlReady($course->studienbereiche) ?>
                            </li>
                        </ul>
                    </td>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
    </form>
<? endif ?>
