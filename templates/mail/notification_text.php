<?
# Lifter010: TODO
?>
<?= _("Diese E-Mail wurde automatisch vom Stud.IP-System verschickt. Sie können auf diese Nachricht nicht antworten.") ?>

<?= _("Sie erhalten hiermit in regelmäßigen Abständen Informationen über Neuigkeiten und Änderungen in Ihren belegten Veranstaltungen.") ?>


<?= _("Über welche Inhalte Sie informiert werden wollen, können Sie hier einstellen:") ?>

<?= URLHelper::getURL('dispatch.php/settings/notification', ['again' => 'yes', 'sso' => $sso]) ?>


<? foreach ($news as $sem_titel => $data) : ?>
<?= sprintf(_("In der Veranstaltung \"%s\" gibt es folgende Neuigkeiten:"), $sem_titel) ?>

<?= URLHelper::getURL('seminar_main.php', ['again' => 'yes', 'sso' => $sso, 'auswahl' => $data[0]['range_id']]) ?>


<? foreach ($data as $module) : ?>
<?= $module['text'] ?>

<?= URLHelper::getURL($module['url'], ['sso' => $sso]) ?>

<? endforeach ?>

<? endforeach ?>

--
<?= _("Diese Nachricht wurde automatisch vom Stud.IP-System generiert. Sie können darauf nicht antworten.") ?>
