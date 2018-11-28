<?php
$current_page = _('Fehler');

$title = _('Fehler! Bitte wenden Sie sich an Ihren Systemadministrator.');
$details = array(htmlReady($exception->getMessage()));

if (Studip\ENV == 'development') {
    $title = "Houston, we've got a problem.";
    $details = array(display_exception($exception, true, true));
}
?>
    <?= MessageBox::exception($title, $details) ?>
    <p>
      <?= _('Zurück zur') ?> <a href="<?= URLHelper::getLink('index.php') ?>"><?= _('Startseite') ?></a>
    </p>
