<?
# Lifter010: TODO
?>
<html>
<head>
    <title><?= htmlReady(PageLayout::getTitle())?></title>
</head>
<body>

<? if (sizeof($dates)) : ?>
    <table cellspacing="0" cellpadding="0" border="1" width="100%">

        <tr>
            <th colspan="5">
                <h2><?= htmlReady(PageLayout::getTitle())?></h2>
            </th>
        </tr>

        <?
        $all_semester = Semester::findAllVisible(false);

        foreach ($dates as $date) :
            if ( ($grenze == 0) || ($grenze < $date['start']) ) {
                foreach ($all_semester as $zwsem) {
                    if ( ($zwsem['beginn'] < $date['start']) && ($zwsem['ende'] > $date['start']) ) {
                        $grenze = $zwsem['ende'];
                        ?>
                        <tr>
                            <td colspan="5">
                                <h3><?= htmlReady($zwsem['name']) ?></h3>
                            </td>
                        </tr>
                    <?
                    }
                }
            }
            ?>
            <tr>
                <td width="20%">
                    <?= htmlReady($date['date']) ?> (<?= htmlReady($date['type']) ?>)
                </td>
                <td width="20%"><?= htmlReady($date['title']) ?></td>
                <td width="20%">
                    <? if (count($date['related_persons']) != $lecturer_count) : ?>
                        <? foreach ($date['related_persons'] as $key => $user_id) {
                            echo ($key > 0 ? ", " : "").htmlReady(get_fullname($user_id));
                        } ?>
                    <? endif ?>
                </td>
                <td width="20%">
                    <? if (count($date['groups']) && count($date['groups']) < $group_count) : ?>
                        <? foreach ($date['groups'] as $key => $statusgruppe_id) {
                            echo ($key > 0 ? ", " : "").htmlReady(Statusgruppen::find($statusgruppe_id)->name);
                        } ?>
                    <? else : ?>
                        <?= _("alle") ?>
                    <? endif ?>
                </td>
                <td width="20%"><?= htmlReady($date['room']) ?></td>
            </tr>
            <? if ($date['description']) : ?>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3"><?= formatReady($date['description'])?></td>
            </tr>
        <? endif ?>
        <? endforeach ?>

    </table>
<? endif ?>
</body>
</html>
