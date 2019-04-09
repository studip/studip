<?
$turnus_list = [
    0 => _("wöchentlich"),
    1 => _("zweiwöchentlich"),
    2 => _("dreiwöchentlich")
];

$output = [];

if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :

$freitext = '';
if (is_array($cycle['freetext_rooms'])){
   $freitext = implode(', ', array_keys($cycle['freetext_rooms']));
}

$plainRooms = '';
if (is_array($cycle['assigned_rooms'])){
    $plainRooms = implode(', ', getPlainRooms($cycle['assigned_rooms']));
}

?>
<raumzeit>
    <startwoche><?= $cycle['week_offset'] ? $cycle['week_offset'] : 0 ?></startwoche>
    <datum><?= $turnus_list[$cycle['cycle']] ?></datum>
    <wochentag><?= getWeekDay($cycle['day'], false) ?></wochentag>
    <zeit><?= $cycle['start_hour'] ?>:<?= $cycle['start_minute'] ?>-<?= $cycle['end_hour'] ?>:<?= $cycle['end_minute'] ?></zeit>
    <raum>
        <gebucht><?= htmlReady($plainRooms) ?></gebucht>
        <freitext><?= htmlReady($freitext) ?></freitext>
    </raum>
</raumzeit>
<? endforeach ?>
<? if (is_array($dates['irregular'])) foreach ($dates['irregular'] as $date) : ?>
<raumzeit>
    <datum><?= date('d.m.Y', $date['start_time']) ?></datum>
    <wochentag><?= getWeekDay(date('w', $date['start_time']),false) ?></wochentag>
    <zeit><?= date('H:i', $date['start_time']) ?>-<?= date('H:i', $date['end_time']) ?></zeit>
    <raum>
        <gebucht><?= htmlReady(implode(', ', getPlainRooms([$date['resource_id'] => 1]))) ?></gebucht>
        <freitext><?= htmlReady($date['raum']) ?></freitext>
    </raum>
</raumzeit>
<? endforeach ?>
