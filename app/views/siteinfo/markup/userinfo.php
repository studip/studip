<?
# Lifter010: TODO
?>
<? if ($error) : ?>
    <em><?= _("Nutzer nicht gefunden.") ?></em>
<? else : ?>
    <a href="<?= URLHelper::getLink('dispatch.php/profile',
                                     ['username' => $username])
              ?>"><?= htmlReady($fullname)?></a>, E-Mail: <?= formatLinks($email)?>
<? endif ?>
