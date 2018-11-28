<section class="contentbox">
    <header>
        <h1><?= _('Was ist Stud.IP?') ?></h1>
    </header>
    <section>
        <div class="formatted-content">
            <?= _('Stud.IP ist ein Open Source Projekt und steht unter der Gnu General Public License (GPL). Das System befindet sich in der ständigen Weiterentwicklung.') ?>

            <? printf(_('Für Vorschläge und Kritik findet sich immer ein Ohr. Wenden Sie sich hierzu entweder an die %sStud.IP Crew%s oder direkt an Ihren lokalen %sSupport%s.'),
                "<a href=\"mailto:studip-users@lists.sourceforge.net\">", "</a>",
                "<a href=\"dispatch.php/siteinfo/show\">", "</a>") ?>
            <br><br>
            <?= _('Um den vollen Funktionsumfang von Stud.IP nutzen zu können, müssen Sie sich am System anmelden.') ?><br>
            <?= _('Das hat viele Vorzüge:') ?><br>

            <ul>
                <li><?= _('Zugriff auf Ihre Daten von jedem internetfähigen Rechner weltweit,') ?>
                <li><?= _('Anzeige neuer Mitteilungen oder Dateien seit Ihrem letzten Besuch,') ?>
                <li><?= _('Eine eigenes Profil im System,') ?>
                <li><?= _('die Möglichkeit anderen Personen Nachrichten zu schicken oder mit ihnen zu chatten,') ?>
                <li><?= _('und vieles mehr.') ?></li>
            </ul>
            <?= _('Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:') ?>
        </div>
    </section>
</section>

<? include('locale/' . $GLOBALS['_language_path'] . '/LC_HELP/pages/nutzung.html'); ?>

<footer>
    <div class="button-group">
        <?= Studip\LinkButton::create(_('Ich erkenne die Nutzungsbedingungen an'), URLHelper::getLink('register2.php')) ?>
        <?= Studip\LinkButton::create(_('Registrierung abbrechen'), URLHelper::getLink('index.php')) ?>
    </div>
</footer>