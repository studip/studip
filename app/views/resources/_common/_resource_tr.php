<?
/**
 * This is a general table row template for resources.
 *
 * Template variables:
 *
 * $resource: A Resource object.
 * $booking_plan_link_on_name: Boolean: Whether the link to the booking plan
 *     shall be wrapped around the name (true) or not (false). In the latter
 *     case, the link will point to the info dialog of the resource instead.
 * $show_global_admin_actions: Boolean: Whether to display actions which are
 *     designed for users with global 'admin' resource permissions.
 *     Defaults to false (do not show actions).
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
 * $checkbox_data: Array: Data for an optional checkbox at the start
 *     of the row. If this is not set no checkbox is shown.
 *     The checkbox will get the resource-ID as value.
 *     Special array indexes:
 *     'name' => The name of the checkbox. This index must be set.
 *     'checked' => Boolean: True, if the checkbox shall be set (checked).
 *         false if it shall be unset (unchecked). Defaults to false.
 *     All other indexes will be added as HTML attributes.
 * $show_picture: Boolean: Whether to display the resource picture or not.
 *     Defaults to false (do not show picture).
 * $show_full_name: Boolean: Whether to display the full name
 *     (with resource type) or just the name field from the database.
 *     Defaults to false (do not show full name).
 * $clipboard_range_type: String: The range type for the drag and drop
 *     functionality of the clipboard system.
 *     Defaults to 'Resource'.
 * $additional_properties: Array: Additional properties
 *     that shall be displayed in extra columns.
 * $additional_columns: Array: Additional columns for the table.
 *     This array contains HTML code for each column (without the td element).
 * $additional_actions: Array: Additional actions for the action menu.
 *     This array contains associative arrays where each of those arrays
 *     has the following structure and indexes:
 *
 *     $position_index => [
 *         0 => Link
 *         1 => Label
 *         2 => Icon
 *         3 => Link attributes
 *     ]
 *
 *     $position_index is a string consisting of four letters with the
 *     first letter being either '0' or another letter. Depending on the
 *     value of $position_index the additional actions are placed
 *     before or after a standard action.
 *     The indexes for the standard actions are:
 *     - '0010': Show details
 *     - '0020': Show booking plan
 *     - '0030': Show semester plan
 *     - '0040': Manage permissions
 *     - '0050': Manage temporary permissions
 *     - '0060': Edit resource
 *     - '0070': Book resource
 *     - '0080': Mass deletion of bookings
 *     - '0090': Export bookings
 *     - '0100': Show files
 *     - '0110': Delete resource
 */
?>
<tr>
    <? if ($checkbox_data && $checkbox_data['name']): ?>
        <?
        if ($checkbox_data['checked']) {
            $checkbox_data['checked'] = 'checked';
        }
        ?>
        <td>
            <input type="checkbox" class="select-resource"
                   value="<?= htmlReady($resource->id) ?>"
                <?= arrayToHtmlAttributes($checkbox_data) ?>>
        </td>
    <? endif ?>
    <td>
        <a href="<?= (
        $booking_plan_link_on_name
            ? $resource->getActionLink('booking_plan')
            : $resource->getActionLink('show')
        ) ?>"
            <?= $user_has_booking_rights ? '' : 'data-dialog' ?>
           data-id="<?= htmlReady($resource->id) ?>"
           data-range_type="<?= $clipboard_range_type
               ? htmlReady($clipboard_range_type)
               : 'Resource' ?>"
           data-name="<?= htmlReady($resource->name) ?>"
           class="clipboard-draggable-item">
            <? if ($show_picture): ?>
                <? $picture_url = $resource->getPictureUrl(); ?>
                <? if ($picture_url): ?>
                    <img class="small-resource-picture"
                         src="<?= htmlReady($picture_url) ?>">
                <? else: ?>
                    <?= $resource->getIcon('clickable') ?>
                <? endif ?>
                <span class="text-bottom">
                    <?= htmlReady(
                        $show_full_name
                            ? $resource->getFullName()
                            : $resource->name
                    ) ?>
                </span>
            <? else: ?>
                <?= htmlReady($resource->name) ?>
                <?= Icon::create('link-intern')->asImg(['class' => 'text-bottom']) ?>
            <? endif ?>
        </a>
        <? if ($resource_tooltip): ?>
            <span class="text-bottom">
                <?= tooltipIcon($resource_tooltip) ?>
            </span>
        <? endif ?>
    </td>
    <? if ($additional_properties): ?>
        <? foreach ($additional_properties as $additional_property): ?>
            <td>
                <? $value = null;
                $property = $resource->getPropertyObject($additional_property);
                if ($property instanceof ResourceProperty) {
                    $value = $property->__toString();
                } elseif($resource->isField($additional_property)) {
                    //There is a SORM field with the name $additional_property.
                    $value = $resource->__get($additional_property);
                }
                ?>
                <?= htmlReady($value) ?>
            </td>
        <? endforeach ?>
    <? endif ?>
    <? if ($additional_columns): ?>
        <? foreach ($additional_columns as $column): ?>
            <td>
                <?= htmlReady($column) ?>
            </td>
        <? endforeach ?>
    <? endif ?>
    <? if ($show_user_actions || $show_autor_actions
        || $show_tutor_actions || $show_admin_actions
        || $show_global_admin_actions || $additional_actions): ?>
        <td class="actions">
            <?
            //Build the actions as array. Ordering is done by array indexes.

            $actions = [];
            $action_menu = ActionMenu::get();
            if ($show_user_actions) {
                $actions['0010'] = [
                    $resource->getActionLink('show'),
                    _('Details'),
                    Icon::create('info-circle'),
                    ['data-dialog' => 'size=auto']
                ];

                $actions['0020'] = [
                    $resource->getActionLink('booking_plan'),
                    _('Belegungsplan'),
                    Icon::create('timetable')
                ];

                $actions['0030'] = [
                    $resource->getActionLink('semester_plan'),
                    _('Semester-Belegungsplan'),
                    Icon::create('timetable'),
                    ['target' => '_blank']
                ];
                if ($show_admin_actions) {
                    $actions['0040'] = [
                        $resource->getActionLink('permissions'),
                        _('Berechtigungen verwalten'),
                        Icon::create('roles2'),
                        ['data-dialog' => 'size=auto']
                    ];
                    $actions['0050'] = [
                        $resource->getActionLink('temporary_permissions'),
                        _('Temporäre Berechtigungen verwalten'),
                        Icon::create('roles2'),
                        ['data-dialog' => 'size=auto']
                    ];
                    $actions['0060'] = [
                        $resource->getActionLink('edit'),
                        _('Bearbeiten'),
                        Icon::create('edit'),
                        ['data-dialog' => 'size=auto']
                    ];
                }
                if ($show_autor_actions) {
                    $actions['0070'] = [
                        $resource->getActionLink(
                            'assign-undecided',
                            [
                                'no_reload' => '1'
                            ]
                        ),
                        _('Buchen'),
                        Icon::create('lock-locked'),
                        [
                            'data-dialog' => 'size=big'
                        ]
                    ];
                    if ($show_global_admin_actions) {
                        $actions['0080'] = [
                            $resource->getActionLink(
                                'delete_bookings',
                                [
                                    'no_reload' => '1'
                                ]
                            ),
                            _('Buchungen löschen'),
                            Icon::create('trash'),
                            ['data-dialog' => 'size=auto']
                        ];
                    }
                }
                if ($show_user_actions) {
                    $actions['0090'] = [
                        $resource->getActionLink('export_bookings'),
                        _('Buchungen exportieren'),
                        Icon::create('file-excel'),
                        ['data-dialog' => 'size=auto']
                    ];
                }
                $actions['0100'] = [
                    $resource->getActionLink('files'),
                    _('Dateien anzeigen'),
                    Icon::create(
                        $resource->hasFiles()
                            ? 'folder-full'
                            : 'folder-empty'
                    ),
                    []
                ];
                if ($show_global_admin_actions) {
                    $actions['0110'] = [
                        $resource->getActionLink('delete'),
                        _('Löschen'),
                        Icon::create('trash'),
                        ['data-dialog' => '']
                    ];
                }
            } else {
                if ($resource->propertyExists('booking_plan_is_public')) {
                    if ($resource->booking_plan_is_public) {
                        $actions['0020'] = [
                            $resource->getActionLink('booking_plan'),
                            _('Belegungsplan anzeigen'),
                            Icon::create('timetable'),
                            [
                                'target' => '_blank'
                            ]
                        ];
                    }
                }
            }
            //Add additional actions for the action menu, if set:
            if (is_array($additional_actions)) {
                $actions = array_merge($actions, $additional_actions);
            }
            //Now we sort the actions by key:
            ksort($actions);

            //And finally we add the actions to the action menu:
            foreach ($actions as $action) {
                if (is_array($action)) {
                    $action_menu->addLink(
                        $action[0],
                        $action[1],
                        $action[2],
                        is_array($action[3]) ? $action[3] : []
                    );
                }
            }
            ?>
            <?= $action_menu->render() ?>
        </td>
    <? endif ?>
</tr>
