<div id="file_edit_window">
    <?= $this->render_partial('file/_folder_aside.php') ?>
    <div id="file_management_forms">
        <form method="post" class="default"
              action="<?= $controller->url_for('/edit_folder/' . $folder->getId()) ?>"
              data-dialog="reload-on-close"
        >
            <?= CSRFProtection::tokenTag() ?>
            <?= $this->render_partial('file/new_edit_folder_form.php') ?>
            <footer data-dialog-button>
                <?= Studip\Button::createAccept(_('Speichern'), 'edit') ?>
                <?= Studip\LinkButton::createCancel(
                    _('Abbrechen'),
                    $controller->url_for((in_array($folder->range_type, ['course', 'institute']) ? $folder->range_type . '/' : '') . 'files/index/' . $folder->parent_id)
                ) ?>
            </footer>
        </form>
    </div>
</div>

<script>
    // On focus the whole folder name shall be selected.
    // The input field with id edit_folder_name is in the view new_edit_folder_form!
    jQuery('#edit_folder_name').select();
</script>
