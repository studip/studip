<form class="default" method="post" action="<?= URLHelper::getLink(); ?>">
    <input type="hidden" name="myCoursesOnly" value="<?= Request::get('myCoursesOnly') ?>">
    <fieldset>
        <legend>
            <?= _('Suche im Veranstaltungsarchiv') ?>
        </legend>
        <label class="col-3">
            <?= _('Name der Veranstaltung') ?>
            <input type="text" minlength="4" name="criteria" placeholder="<?= _('Veranstaltung suchen') ?>"
                   value="<?= htmlReady($criteria) ?>" autofocus>
        </label>
        <label class="col-3">
            <?= _('Semester') ?>
            <select name="selectedSemester">
                <option value=""
                    <?= ($selectedSemester == '') ? 'selected="selected"' : '' ?>>
                    <?= _('alle') ?>
                </option>
                <? foreach($availableSemesterNames as $semesterName) : ?>
                <option value="<?= htmlReady($semesterName[0]) ?>"
                    <?= ($selectedSemester == $semesterName[0]) ? 'selected="selected"' : '' ?>>
                    <?= htmlReady($semesterName[0]) ?>
                </option>
                <? endforeach ?>
            </select>
        </label>
        <label class="col-3">
            <?= _('Einrichtung') ?>
            <select name="selectedDepartment" class="nested-select">
                <option value="" class="nested-item-header"
                    <?= ($selectedDepartment == '') ? 'selected="selected"' : '' ?>>
                    <?= _('alle') ?>
                </option>
                <? foreach($availableDepartments as $department) : ?>
                <option value="<?= htmlReady($department->name) ?>"
                    class="nested-item-header"
                    <?= ($selectedDepartment == $department->name) ? 'selected="selected"' : '' ?>>
                    <?= htmlReady($department->name) ?>
                </option>
                    <? foreach($department->sub_institutes as $subDepartment) : ?>
                    <option value="<?= htmlReady($subDepartment->name) ?>"
                        class="nested-item nested-item-level-1"
                        <?= ($selectedDepartment == $subDepartment->name) ? 'selected="selected"' : '' ?>>
                        <?= htmlReady($subDepartment->name) ?>
                    </option>
                    <? endforeach ?>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>
    <footer>
        <?= \Studip\Button::create(_('Suchen'), '') ?>
        <?= Studip\LinkButton::create(_('Zurücksetzen'), URLHelper::getURL('dispatch.php/search/archive')) ?>
    </footer>
</form>

<? if ($foundCourses) : ?>
    <br>
    <table class="default withdetails">
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Lehrende') ?></th>
            <th><?= _('Einrichtungen') ?></th>
            <th><?= _('Semester') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
    <? foreach ($foundCourses as $course) : ?>
        <tr <? if (count($foundCourses) == 1) : ?>class="open"<? endif ?> >
            <td onclick="jQuery(this).closest('tr').toggleClass('open'); return false;">
                <?= htmlReady($course->name) ?>
            </td>
            <td><?= htmlReady($course->dozenten) ?></td>
            <td><?= htmlReady($course->institute) ?></td>
            <td><?= htmlReady($course->semester) ?></td>
            <td>
                <? if(archiv_check_perm($course->id)) : ?>
                <a href="<?= $controller->url_for(
                                'archive/overview',
                                $course->id
                                ) ?>" data-dialog>
                    <?= Icon::create('info-circle', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>

                <? if ($course->archiv_file_id and archiv_check_perm($course->id)): ?>
                <a href="<?= FileManager::getDownloadLinkForArchivedCourse($course, false) ?>">
                    <?= Icon::create('file-archive', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
                <? if ($course->archiv_protected_file_id and in_array(archiv_check_perm($course->id), ['tutor', 'dozent', 'admin'])): ?>
                <a href="<?= FileManager::getDownloadLinkForArchivedCourse($course, true) ?>">
                    <?= Icon::create('file-archive', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
                <? if (archiv_check_perm($course->id)): ?>
                <a href="<?= $controller->url_for(
                                'archive/forum',
                                $course->id
                                ) ?>" data-dialog>
                    <?= Icon::create('forum', 'clickable')->asImg('16px') ?>
                </a>
                <a href="<?= $controller->url_for(
                                'archive/wiki',
                                $course->id
                                ) ?>" data-dialog>
                    <?= Icon::create('wiki', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
                <? if (archiv_check_perm($course->id) == 'admin'): ?>
                <a href="<?= URLHelper::getLink(
                         'dispatch.php/archive/delete/' . $course->id,
                         [
                             'criteria' => $criteria,
                             'selectedSemester' => $selectedSemester,
                             'selectedDepartment' => $selectedDepartment
                         ]
                         ) ?>"
                   title="<?= _('Löschen') ?>"
                   onclick="return STUDIP.Dialog.confirmAsPost('<?=
                       sprintf(
                           _('Soll die Veranstaltung %1$s wirklich aus dem Archiv gelöscht werden?'),
                           htmlReady($course->name)
                       ) ?>', this.href);">
                    <?= Icon::create('trash', 'clickable')->asImg('16px') ?>
                </a>
                <? endif ?>
            </td>
        </tr>
        <tr class="details nohover">
            <td colspan="5" class="detailscontainer">
                <ul class="default nohover">
                    <li>
                        <strong><?= _('Fakultät') . ':' ?></strong>
                        <?= htmlReady($course->fakultaet) ?>
                    </li>
                    <li>
                        <strong><?= _('Bereich')  . ':' ?></strong>
                        <?= htmlReady($course->studienbereiche) ?>
                    </li>
                </ul>
            </td>
        </tr>
    <? endforeach ?>
    </table>
<? endif ?>
