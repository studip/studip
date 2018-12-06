<?php
$details = $exception->getDetails();
array_unshift($details, htmlReady($exception->getMessage()));
?>
<?= MessageBox::exception(_('Zugriff verweigert'), $details) ?>
<p>
    <?= sprintf(
        _('Zurück zur %sStartseite%s'),
        '<a href="' . URLHelper::getLink('index.php') . '">',
        '</a>'
    ) ?>
</p>
