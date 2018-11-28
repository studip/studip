<?
# Lifter010: TODO
?>
<div style="background-color:#DEE2E8; padding:10px;">
    <h3><?= _('Verfügbares Markup')?></h3>
    <p><?= sprintf(_('Zusätzlich zu den üblichen %sSchnellformatierungen%s und dem Wiki-Markup ist folgendes Markup verfügbar:'), '<a href="http://hilfe.studip.de/index.php/Basis/VerschiedenesFormat">', '</a>')?></p>
    <dl>
        <dt>[lang=<em>language</em>]<em>...</em>[/lang]</dt>
        <dd><?= sprintf(_('Nur wenn %s der Nutzersprache entspricht, wird der Text (%s) zwischen den Tags angezeigt'),
                            '<em>language</em>', '<em>...</em>')?></dd>
        <dt>[style=<em>definition</em>]<em>...</em>[/style]</dt>
        <dd><?= sprintf(_('Die durch %s angegebenen CSS-Gestaltungsangaben werden dem umschlossenen Bereich (%s) zugewiesen.'),
                            '<em>definition</em>', '<em>...</em>')?></dd>
        <dt>(:version:)</dt>
        <dd><?= _('Die Angabe der verwendeten Stud.IP-Version.')?></dd>
        <dt>(:uniname:)</dt>
        <dd><?= _('Der Name des Standortes gemäß der Konfiguration.')?></dd>
        <dt>(:unicontact:)</dt>
        <dd><?= _('Der administrative Kontakt gemäß der Konfiguration.')?></dd>
        <dt>(:userinfo <em>user</em>:)</dt>
        <dd><?= sprintf(_('Ausgabe von Vor- und Nachnamen verlinkt mit dem Profil und der E-Mail-Adresse des mit %s angegebenen Nutzers.'),'<em>user</em>')?></dd>
        <dt>(:userlink <em>user</em>:)</dt>
        <dd><?= sprintf(_('Ausgabe von Vor- und Nachnamen verlinkt mit dem Profil des mit %s angegebenen Nutzers.'),'<em>user</em>')?></dd>
        <dt>(:rootlist:)</dt>
        <dd><?= _('Ausgabe von Vor- und Nachnamen verlinkt mit dem Profil und der E-Mail-Adresse aller Nutzer mit Root-Status.')?></dd>
        <dt>(:adminlist:)</dt>
        <dd><?= _('Ausgabe von Vor- und Nachnamen verlinkt mit dem Profil und der E-Mail-Adresse aller Nutzer mit Admin-Status.')?></dd>
        <dt>(:coregroup:)</dt>
        <dd><?= _('Ausgabe des Inhaltes von http://www.studip.de/crew.php.')?></dd>
        <dt>(:toplist <em>subject</em>:)</dt>
        <dd><?= sprintf(_('Ausgabe von Ranglisten für die mit %s angegebenen Kriterien, die die Ausprägungen:'),
                        '<em>subject</em>')?>
            <ul>
                <li>mostparticipants</li>
                <li>recentlycreated</li>
                <li>mostdocuments</li>
                <li>mostpostings</li>
                <li>mostvisitedhomepages</li>
            </ul>
            haben können.
        </dd>
        <dt>(:indicator <em>subject</em>:)</dt>
        <dd><?= sprintf(_('Ausgabe von mit %s spezifizierten Kennzahlen aus den folgenden Möglichkeiten:'),
                        '<em>subject</em>')?>
            <ul>
                <li>seminar_all</li>
                <li>seminar_archived</li>
                <li>institute_secondlevel_all</li>
                <li>institute_firstlevel_all</li>
                <li>user_admin</li>
                <li>user_dozent</li>
                <li>user_tutor</li>
                <li>user_autor</li>
                <li>posting</li>
                <li>document</li>
                <li>link</li>
                <li>litlist</li>
                <li>termin</li>
                <li>news</li>
                <li>vote</li>
                <li>test</li>
                <li>evaluation</li>
                <li>wiki_pages</li>
                <li>lernmodul</li>
                <li>resource</li>
            </ul>
        </dd>
        <dt>(:history:)</dt>
        <dd><?= _('Ausgabe der history.txt')?></dd>
        <dt>(:terms:)</dt>
        <dd><?= _('Ausgabe der Nutzungsbedingungen')?></dd>
    </dl>
</div>
