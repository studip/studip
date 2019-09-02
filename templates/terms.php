<section class="contentbox">
    <header>
        <h1><?= _('Was ist Stud.IP?') ?></h1>
    </header>
    <section>
        <?= _('Stud.IP ist ein Open Source Projekt und steht unter der GNU General Public License (GPL). Das System befindet sich in der ständigen Weiterentwicklung.') ?>

        <? printf(
            _('Für Vorschläge und Kritik findet sich immer ein Ohr. Wenden Sie sich hierzu entweder an die %sStud.IP Crew%s oder direkt an Ihren lokalen %sSupport%s.'),
            '<a href="mailto:studip-users@lists.sourceforge.net">',
            '</a>',
            '<a href="' . URLHelper::getLink('dispatch.php/siteinfo/show') . '">',
            '</a>'
        ) ?>
        <br><br>
        <?= _('Um den vollen Funktionsumfang von Stud.IP nutzen zu können, müssen Sie sich am System anmelden.') ?><br>
        <?= _('Das hat viele Vorzüge:') ?><br>

        <ul>
            <li><?= _('Zugriff auf Ihre Daten von jedem internetfähigen Rechner weltweit,') ?></li>
            <li><?= _('Anzeige neuer Mitteilungen oder Dateien seit Ihrem letzten Besuch,') ?></li>
            <li><?= _('Ein eigenes Profil im System,') ?></li>
            <li><?= _('die Möglichkeit anderen Personen Nachrichten zu schicken oder mit ihnen zu chatten,') ?></li>
            <li><?= _('und vieles mehr.') ?></li>
        </ul>
        <?= _('Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:') ?>
    </section>
</section>

<?php
    $lang = $GLOBALS['_language_path'] ?: 'de';
    include("locale/{$lang}/LC_HELP/pages/nutzung.html");
