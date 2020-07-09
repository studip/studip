<? if ($defined_variables) : ?>
    <p>
        <?= sprintf(
            _('Erstellung eines Bibliothekseintrages vom Typ „%s“'),
            htmlReady($document_type['display_name'][$user_language] ?: $document_type['name'])
        ) ?>
    </p>
    <?= $this->render_partial(
        'library_file/_add_edit_form',
        [
            'form_action' => $controller->link_for('library_file/create/' . $folder_id)
        ]
    ) ?>
<? endif ?>
