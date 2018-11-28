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
if (Request::getArray('fileref_id')) {
    $options['fileref_id'] = Request::getArray('fileref_id');
}
if (Request::get('isfolder')) {
    $options['isfolder'] = Request::get('isfolder');
}
if (Request::get('copymode')) {
    $options['copymode'] = Request::get('copymode');
}
?>

<script>
jQuery(function ($) {
    $('#folderchooser_course_search select option').on('click', function () {
    	$('#folderchooser_course_search').submit();
    });
});
</script>

<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
    <form id="folderchooser_course_search"
          action="<?= $controller->link_for('file/choose_folder_from_course/', $options) ?>"
          data-dialog>
        <?= QuickSearch::get('course_id', new StandardSearch('AnySeminar_id'))
            ->fireJSFunctionOnSelect("function () { jQuery('#folderchooser_course_search').submit(); }")
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
            </tr>
        </thead>
        <tbody>
        <? foreach ($courses as $course) : ?>
            <tr>
                <td>
                    <a href="<?= $controller->link_for('file/choose_folder_from_course/', array_merge($options, ['course_id' => $course->id])) ?>" data-dialog>
                        <?= CourseAvatar::getAvatar($course->id)->getImageTag(Avatar::MEDIUM, ['style' => 'width: 50px; height: 50px;']) ?>
                    </a>
                </td>
                <td>
                    <a href="<?= $controller->link_for('file/choose_folder_from_course/', array_merge($options, ['course_id' => $course->id])) ?>" data-dialog>
                        <?= htmlReady($course->getFullname()) ?>
                    </a>
                </td>
                <td>
                    <?= htmlReady($course->start_semester->name) ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>

<footer data-dialog-button>
    <?= Studip\LinkButton::create(
        _('Zurück'),
        $controller->url_for('file/choose_destination/' . $options['copymode'], $options),
        ['data-dialog' => 'size=auto']
    ) ?>
</footer>
