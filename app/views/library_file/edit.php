<? if ($defined_variables) : ?>
    <?= $this->render_partial(
        'library_file/_add_edit_form',
        [
            'form_action' => $controller->link_for('library_file/edit/' . $file_ref->id)
        ]
    ) ?>
<? endif ?>
