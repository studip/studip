<form method="post" class="default" data-dialog="reload-on-close" action="<?= $controller->url_for('file/new_folder/' . $parent_folder_id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('file/new_edit_folder_form.php', [
        'name'            => $name,
        'description'     => $description,
        'folder_types'    => $folder_types,
        'new_folder_form' => true,
    ]) ?>
    <footer data-dialog-button>
        <? if ($show_confirmation_button): ?>
            <?= Studip\Button::createAccept(_('Trotzdem erstellen'), 'force_creation') ?>
        <? else: ?>
            <?= Studip\Button::createAccept(_('Erstellen'), 'create') ?>
        <? endif ?>
    </footer>
</form>
