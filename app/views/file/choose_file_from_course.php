<?php
$options = [];
if (Request::get('to_plugin')) {
    $options['to_plugin'] = Request::get('to_plugin');
}
if (Request::get('from_plugin')) {
    $options['from_plugin'] = Request::get('from_plugin');
}
if (Request::get('range_type')) {
    $options['range_type'] = Request::get('range_type');
}
?>

<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
    <form id="filechooser_course_search"
          action="<?= $controller->link_for('file/choose_file_from_course/' . $folder_id) ?>"
          data-dialog>
        <?= QuickSearch::get('course_id', new StandardSearch('AnySeminar_id'))
            ->fireJSFunctionOnSelect("function () { jQuery('#filechooser_course_search').submit(); }")
            ->setInputStyle('width: calc(100% - 40px); margin: 20px;')
            ->render() ?>
    </form>
<? else : ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Bild') ?></th>
                <th><?= _('Name') ?></th>
                <th><?= _('Semester') ?></th>
                <th class="actions"><?= _('Zum Dateibereich') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($courses as $course) : ?>
            <tr>
                <td class="avatar">
                    <a href="<?= $controller->link_for('file/choose_file_from_course/' . $folder_id, array_merge($options, ['course_id' => $course->id])) ?>" data-dialog>
                        <?= CourseAvatar::getAvatar($course->id)->getImageTag(Avatar::MEDIUM, ['style' => 'width: 50px; height: 50px;']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= $controller->link_for('file/choose_file_from_course/' . $folder_id, array_merge($options, ['course_id' => $course->id])) ?>" data-dialog>
                        <?= htmlReady($course->getFullname()) ?>
                    </a>
                </td>
                <td>
                    <?= htmlReady($course->start_semester->name) ?>
                </td>
                <td class="actions">
                    <a href="<?= $controller->link_for('file/choose_file_from_course/' . $folder_id, array_merge($options, ['course_id' => $course->id])) ?>" data-dialog>
                        <?= Icon::create('folder-full', Icon::ROLE_CLICKABLE)->asImg(30) ?>
                    </a>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>

<footer data-dialog-button>
    <?= Studip\LinkButton::create(
        _('Zurück'),
         $controller->url_for('/add_files_window/' . Request::get('to_folder_id'), $options),
         ['data-dialog' => '']
    ) ?>
</footer>
