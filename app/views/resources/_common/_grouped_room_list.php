<?
/**
Template parameters:
- $title: The list title
- $grouped_rooms: The rooms, grouped by RoomManager::groupRooms
- $link_template: An optional link template where the room-ID is
       represented by the only "%s" placeholder.
       If $link_template is not set, the link to the booking plan
       of the room is generated.
- $show_in_dialog: Whether to show the room link in a dialog (true)
       or not (false).
*/
?>
<? if ($grouped_rooms) : ?>
    <? if ($title) : ?>
        <h1><?= htmlReady($title) ?></h1>
    <? endif ?>
    <? foreach ($grouped_rooms as $group) : ?>
        <?
        $location = $group['location'];
        $buildings = $group['buildings'];
        ?>
        <div class="studip-widget-wrapper">
            <article class="studip">
                <header><h1><?= htmlReady($location->name) ?></h1></header>
                <? foreach ($buildings as $building_group) : ?>
                    <?
                    $building = $building_group['building'];
                    $rooms = $building_group['rooms'];
                    ?>
                    <article class="studip toggle">
                        <header><h1><a href="#"><?= htmlReady($building->name) ?></a></h1></header>
                        <section>
                            <table class="default">
                                <thead>
                                    <tr><th><?= _('Raum') ?></th></tr>
                                </thead>
                                <tbody>
                                    <? foreach ($rooms as $room) : ?>
                                        <?
                                        $room_link = '';
                                        if ($link_template) {
                                            $room_link = $controller->link_for(
                                                sprintf(
                                                    $link_template,
                                                    $room->id
                                                )
                                            );
                                        } else {
                                            $room_link = $room->getActionLink('booking_plan');
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= $room_link ?>"
                                                   <?= $show_in_dialog ? 'data-dialog="size=big"' : '' ?>>
                                                        <?= htmlReady($room->name) ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <? endforeach ?>
                                </tbody>
                            </table>
                        </section>
                    </article>
                <? endforeach ?>
            </article>
        </div>
    <? endforeach ?>
<? endif ?>
