<? if (is_array($rooms) && count($rooms)): ?>
    <? foreach ($rooms as $room): ?>
        <?= $this->render_partial(
            'resources/_common/_room_search_result.php',
            [
                'room' => $room,
                'show_user_actions' => $room->userHasPermission(
                    $current_user,
                    'user'
                ),
                'show_autor_actions' => $room->userHasPermission(
                    $current_user,
                    'autor'
                ),
                'show_tutor_actions' => $room->userHasPermission(
                    $current_user,
                    'tutor'
                ),
                'show_admin_actions' => $room->userHasPermission(
                    $current_user,
                    'admin'
                ),
                'cliboard_widget_id' => $clipboard_widget_id
            ]
        ) ?>
    <? endforeach ?>
<? else: ?>
    <? if ($form_submitted && !$has_errors): ?>
        <?= MessageBox::info(
            _('Es wurden keine Räume gefunden, die zu den angegebenen Suchkriterien passen!')
        ) ?>
    <? endif ?>
    <? if (!$form_submitted): ?>
        <?= MessageBox::info(
            _('Wählen Sie Suchkriterien oder ein Element im Ressourcenbaum, um Räume zu finden.')
        ) ?>
    <? endif ?>
<? endif ?>
