<?= MessageBox::error("DIES IST NUR EIN BEISPIELTEXT. NICHT OHNE LOKALE ANPASSUNGEN VERWENDEN!") ?>
<table width="100%" border="0" cellspacing="10" cellpadding="10">
    <colgroup>
        <col style="width: 47%">
        <col style="width: 6%">
        <col style="width: 47%">
    </colgroup>
    <tr>
        <td colspan="3">
            <h1>Änderungen zur Sichtbarkeit in Stud.IP</h1>
            <p>Ab sofort sind Ihre Daten für andere NutzerInnen nur
                noch mit Ihrem Einverständnis zugänglich. Sie haben <em>jetzt</em>
                die Möglichkeit, sich zu entscheiden, weiterhin sichtbar zu sein und Stud.IP
                wie gewohnt zu nutzen, oder unsichtbar zu sein, dafür aber nicht mehr alle
                Funktionen verwenden zu können.</p>
            <p>Sie können diese Einstellung jederzeit unter &quot;Persönliche Einstellungen&quot;
                ändern.</p>
        </td>
    </tr>
    <tr>
        <td valign="top">Wenn Sie sichtbar sind, dann</td>
        <td></td>
        <td valign="top">Wenn Sie unsichtbar sind, dann</td>
    </tr>
    <tr>
        <td valign="top">
            <ul>
                <li>können Sie alle Kommunikationsmöglichkeiten wie gewohnt
                    nutzen,
                </li>
                <li>können Sie in Ihrem Profil trotzdem weitestgehend
                    entscheiden, was andere über Sie erfahren können,
                </li>
                <li>können Sie von anderen gefunden und kontaktiert werden,</li>
                <li>leisten Sie einen Beitrag dazu, Stud.IP weiterhin zu einer aktiven
                    und kommunikativen Plattform zu machen.
                </li>
            </ul>
        </td>
        <td></td>
        <td valign="top">
            <ul>
                <li>können Sie nicht mehr über die Personensuche gefunden werden,</li>
                <li>können Sie nicht mehr in der "Wer-ist-online"-Liste erscheinen,</li>
                <li>können Sie nicht mehr Ihr Profil nutzen,</li>
                <li>können Sie nicht mehr Ihre E-Mail-Adresse, Ihr Gästebuch, Ihre Stud.IP-Punkte etc. anderen
                    zugänglich machen,
                </li>
                <li>können Sie nicht mehr im Adressbuch anderer NutzerInnen stehen.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            Achtung! Grundsätzlich und unabhängig davon, ob Sie sichtbar oder unsichtbar sind, gilt:
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <ul>
                <li>Sie können Stud.IP aktiv nutzen und sich an Veranstaltungen, Foren etc. beteiligen</li>
                <li>Teilnehmendenliste von Veranstaltungen sind nur dann für die Teilnehmenden zugänglich, wenn alle
                    einverstanden sind
                </li>
                <li>
                    Sobald Sie im System aktiv werden - d.h. Forumsbeiträge verfassen, sich an nicht-anonymen Umfragen
                    beteiligen, Mails verschicken etc. - wird Ihr Name dabei angegeben und es lässt sich nicht
                    vermeiden, dass andere Nutzerinnen und Nutzer indirekt erkennen können, ob Sie sichtbar oder
                    unsichtbar sind.
                </li>
            </ul>
        </td>
    </tr>
    <tr>
        <td style="background:#ddffdd; border:1px solid black;" valign="top">
            <p><b>Ich möchte sichtbar sein und alle Möglichkeiten von Stud.IP nutzen können.</b></p>
            <p>Ich akzeptiere damit, dass die in den <a href="<?= URLHelper::getURL('datenschutz.php') ?>"
                                                        class="link-intern" target="_blank">Erläuterungen
                    zum Datenschutz</a> aufgeführten Informationen
                anderen zugänglich sind.</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <?= \Studip\LinkButton::create('Sichtbar werden', URLHelper::getURL('?vis_state=yes&vis_cmd=apply')) ?>
        </td>
        <td></td>
        <td style="background:#ffdddd; border:1px solid black;" valign="top">
            <p><b>Ich möchte unsichtbar sein und nehme Einschränkungen in der Nutzung in Kauf.</b>
            <p>Ich nehme damit zur Kenntnis, dass meine persönlichen Daten wie in den <a
                        href="<?= URLHelper::getLink('datenschutz.php') ?>" class="link-intern"
                        target="_blank">Erläuterungen zum Datenschutz</a>
                beschrieben und begründet dennoch Administrator/-innen und, in Teilen, den
                Lehrenden meiner Veranstaltungen zugänglich sind.</p>
            <p>&nbsp;</p>
            <?= \Studip\LinkButton::create('Unsichtbar werden', URLHelper::getURL('?vis_state=no&vis_cmd=apply')) ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <p>Sie können diese Entscheidung jederzeit unter "Persönliche Einstellungen" wieder ändern.
        </td>
    </tr>
</table>
