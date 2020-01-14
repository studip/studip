<?= $this->render_partial(
    'resources/global_locks/_add_edit_form.php',
    [
        'action_link' => URLHelper::getLink('dispatch.php/resources/global_locks/add')
    ]
) ?>
