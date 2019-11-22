<?
# Lifter010: TODO
?>
<?= $message ?>

<? if (isset($attachments) && count($attachments)) : ?>

    <?= _("Dateianhänge:") ?>

    <? foreach ($attachments as $attachment) : ?>
        <?= $attachment->name . ' (' . relsize($attachment->file->size, false) . ')' ?>

        <?= $attachment->getDownloadURL() ?>


    <? endforeach; ?>
<? endif; ?>


--
<? if ($snd_fullname) : ?>
    <?= sprintf(_('Diese E-Mail ist eine Kopie einer systeminternen Nachricht, die in Stud.IP von %s (%s) an %s (%s) versendet wurde.'), $snd_fullname, $snd_email, $rec_fullname, $rec_email) ?>
<? else : ?>
    <?= sprintf(_("Diese E-Mail ist eine Kopie einer systeminternen Nachricht, die in Stud.IP an %s versendet wurde."), $rec_fullname) ?>
<? endif ?>
<?= sprintf(_("Sie erreichen Stud.IP unter %s"), $GLOBALS['ABSOLUTE_URI_STUDIP']) ?>
