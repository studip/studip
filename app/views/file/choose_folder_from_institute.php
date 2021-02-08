<?php
$options = array_filter([
    'to_plugin'   => Request::get('to_plugin'),
    'from_plugin' => Request::get('from_plugin'),
    'range_type'  => Request::get('range_type'),
    'fileref_id'  => Request::getArray('fileref_id'),
    'isfolder'    => Request::get('isfolder'),
    'copymode'    => Request::get('copymode'),
], function ($value) {
    return $value !== null;
});
?>

<? if ($GLOBALS['perm']->have_perm("admin")) : ?>
<form id="folderchooser_institute_search" method="post"
      action="<?= $controller->link_for('/choose_folder_from_institute') ?>"
      data-dialog>
    <?= QuickSearch::get('Institut_id', $instsearch)
        ->fireJSFunctionOnSelect("function () { jQuery('#folderchooser_institute_search').submit(); }")
        ->setInputStyle('width: calc(100% - 40px); margin: 20px;')
        ->render()
    ?>
<? else : ?>
<form action="#" method="post" data-dialog>
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Bild') ?></th>
                <th><?= _('Name') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach (Institute::getMyInstitutes($GLOBALS['user']->id) as $institut) : ?>
            <tr>
                <td>
                    <input type="image" class="undecorated"
                           style="width: 50px; height: 50px;"
                           formaction="<?= $controller->link_for('file/choose_folder_from_institute') ?>"
                           name="Institut_id"
                           value="<?= htmlReady($institut['Institut_id']) ?>"
                           src="<?= htmlReady(
                                InstituteAvatar::getAvatar($institut['Institut_id'])->getUrl(Avatar::MEDIUM)
                                ) ?>">
                </td>
                <td>
                    <button formaction="<?= $controller->link_for('file/choose_folder_from_institute') ?>" name="Institut_id" value="<?= htmlReady($institut['Institut_id']) ?>" class="undecorated">
                        <?= htmlReady($institut['Name']) ?>
                    </button>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>

<? foreach ($options as $key => $value): ?>
    <?= addHiddenFields($key, $value) ?>
<? endforeach; ?>

    <footer data-dialog-button>
        <!-- neu -->
        <?= Studip\Button::create(_('Zurück'), [
            'formaction'  => $controller->url_for('/choose_destination/' . $options['copymode']),
            'data-dialog' => 'size=auto',
        ]) ?>
    </footer>
</form>

<script>
jQuery(function () {
    $('#folderchooser_institute_search select option').on('click', function () {
    	$('#folderchooser_institute_search').submit();
    });
});
</script>
