<?
/**
 * This is a specialisation of the _resource_tr template for rooms.
 *
 * Template variables:
 *
 * $room: A Room object.
 * $show_admin_actions: Boolean: Whether to display actions which are
 *     designed for users with 'admin' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_tutor_actions: Boolean: Whether to display actions which are
 *     designed for users with 'tutor' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_autor_actions: Boolean: Whether to display actions which are
 *     designed for users with 'autor' resource permissions.
 *     Defaults to false (do not show actions).
 * $show_user_actions: Boolean: Whether to display actions which are
 *     designed for users with 'user' resource permissions.
 *     Defaults to false (do not show actions).
 * $user_has_booking_rights: Boolean: Whether the user for which this template
 *     is rendered has booking rights on the resource (true) or not (false).
 * $show_room_picture: Boolean: Whether to display the room picture or not.
 *     Defaults to false (do not show picture).
 * $additional_properties: Array: Additional properties
 *     that shall be displayed in extra columns.
 * $additional_columns: Array: Additional columns for the table.
 * $additional_actions: Array: Additional actions for the action menu.
 *     This array contains associative arrays where each of those arrays
 *     has the following structure and indexes:
 *     [
 *         0 => Link
 *         1 => Label
 *         2 => Icon
 *         3 => Link attributes
 *     ]
 */
?>

<?
$room_actions = [];
if ($room->requestable && $show_autor_actions) {
    $room_actions = [
        '0071' => [
            $room->getActionLink('request_list'),
            _('Anfragen auflösen'),
            Icon::create('room-request'),
            ['target' => '_blank']
        ]
    ];
} ?>

<?= $this->render_partial(
    'resources/_common/_resource_tr.php',
    [
        'checkbox_data' => $checkbox_data,
        'resource' => $room,
        'booking_plan_link_on_name' => true,
        'resource_tooltip' => $room_tooltip,
        'show_global_admin_actions' => $show_global_admin_actions,
        'show_admin_actions' => $show_admin_actions,
        'show_tutor_actions' => $show_tutor_actions,
        'show_autor_actions' => $show_autor_actions,
        'show_user_actions' => $show_user_actions,
        'user_has_booking_rights' => $user_has_booking_rights,
        'show_picture' => true,
        'show_full_name' => false,
        'additional_properties' => ['seats'],
        'clipboard_range_type' => 'Room',
        'additional_actions' => (
            is_array($additional_actions)
            ? array_merge(
                $room_actions,
                $additional_actions
            )
            : $room_actions
        )
    ]
) ?>
