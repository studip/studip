<h2><?= _('Willkommen beim Installationsassistenten für Stud.IP!') ?></h2>

<p>
    <?= sprintf(
        _('Dieser Assistent führt Sie in %u Schritten durch die Installation '
        . 'von Stud.IP. Nach dem letzten Schritt haben Sie eine voll '
        . 'funktionsfähige Stud.IP-Installation mit Datenbank und von Ihnen '
        . 'ausgewählten Beispiel-Inhalten für den ersten Testbetrieb.'),
        count($steps)
    ) ?>

    <?= _('Die Installation dauert ca. 10 Minuten.') ?>
</p>

<p>
    <?= _('Sie benötigen für die Installation') ?>
    <ul>
        <li><?= _('eine Datenbank (MySQL/MariaDB)') ?></li>
        <li>
            <?= _('Zugriff auf den Server (als Server-Administrator oder über '
                . 'Ihren Hoster)') ?>
        </li>
    </ul>
</p>

<p>
    <?= _('Bitte halten Sie folgende Informationen bereit:') ?>

    <ul>
        <li><?= _('Datenbank-Host') ?></li>
        <li><?= _('Datenbank-Name') ?></li>
        <li><?= _('Datenbank-Nutzername') ?></li>
        <li><?= _('Datenbank-Passwort') ?></li>
    </ul>
</p>


<p>
    <?= sprintf(
        _('Eine ausführliche Installationsanleitung und weiterführende Hilfen '
        . 'finden Sie auf den %sHilfe-Seiten von Stud.IP%s und über die Fußleiste '
        . 'des Assistenten.'),
        '<a href="https://hilfe.studip.de/admin/Admins/Installationsanleitung" class="link-extern" target="_blank">',
        '</a>'
    ) ?>
</p>

<p>
    <?= _('Klicken Sie auf „Assistent starten“, wenn Sie mit der Installation '
        . 'beginnen wollen!') ?>
</p>
