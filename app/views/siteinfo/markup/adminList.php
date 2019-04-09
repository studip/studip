<?
# Lifter010: TODO
?>
<? if ($error) : ?>
    <em><?= _("keine. Na sowas. Das kann ja eigentlich gar nicht sein...") ?></em>
<? else : ?>
    <? $current_head = "" ?>
    <? $switch_column = count($admins)/2 ?>
    <? $i = 1 ?>
    <table width="100%">
        <tr>
            <td style="vertical-align: top">
        <? foreach($admins as $admin) : ?>
            <? if ($current_head != $admin['institute']) :?>
                <? $current_head = $admin['institute'] ?>
                <? if ($i>$switch_column) : ?>
                    </td>
                    <td style="vertical-align: top">
                    <? $i = 0 ?>
                <? endif ?>
                <h4><?= htmlReady($current_head) ?></h4>
            <? endif ?>
            <a href="<?= URLHelper::getLink('dispatch.php/profile',
                                             ['username' => $admin['username']])
                      ?>"><?= htmlReady($admin['fullname'])?></a>, E-Mail:<?= formatLinks($admin['Email']) ?><br>
            <? $i++ ?>
        <? endforeach ?>
            </td>
        </tr>
    </table>
<? endif ?>
