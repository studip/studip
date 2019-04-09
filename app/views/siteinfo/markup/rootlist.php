<?
# Lifter010: TODO
?>
<? if ($error) : ?>
    <em><?= _("keine. Na sowas. Das kann ja eigentlich gar nicht sein...") ?></em>
<? else : ?>
    <ul>
        <? foreach($users as $user) : ?>
            <li>
                <a href="<?= URLHelper::getLink('dispatch.php/profile',
                                                 ['username' => $user['username']])
                          ?>"><?= htmlReady($user['fullname']) ?></a>, E-Mail:
                <?= formatLinks($user['Email']) ?>
            </li>
        <? endforeach ?>
    </ul>
<? endif ?>
