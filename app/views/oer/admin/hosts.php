<table class="default serversettings">
    <caption>
        <?= _('Lernmarktplatz-Server') ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Adresse') ?></th>
            <th title="<?= _('Ein Hash des Public-Keys des Servers.') ?>"><?= _('Key-Hash') ?></th>
            <th><?= _('Index-Server') ?></th>
            <th><?= _('Aktiv') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($hosts as $host) : ?>
            <tr id="host_<?= $host->getId() ?>" data-host_id="<?= $host->getId() ?>">
                <td>
                    <? if ($host->isMe()) : ?>
                        <?= Icon::create("home", Icon::ROLE_INFO)->asImg(20, ['class' => "text-bottom", 'title' => _('Das ist Ihr Stud.IP')]) ?>
                    <? endif ?>
                    <?= htmlReady($host['name']) ?></td>
                <td>
                    <a href="<?= htmlReady($host['url']) ?>" target="_blank">
                        <?= Icon::create("link-extern", Icon::ROLE_CLICKABLE)->asImg(16, ['class' => "text-bottom"]) ?>
                        <?= htmlReady($host['url']) ?>
                    </a>
                </td>
                <td>
                    <?= $host['public_key'] ? md5($host['public_key']) : "" ?>
                    <? if (strpos($host['public_key'], "\r") !== false) : ?>
                        <?= Icon::create("exclaim", Icon::ROLE_STATUS_RED)->asImg(20, ['class' => "text-bottom", 'title' => _('Der Key hat ein Carriage-Return Zeichen, weshalb der Hash des Public-Keys vermutlich vom Original-Hash abweicht.')]) ?>
                    <? endif ?>
                </td>
                <td class="index_server">
                    <? if ($host->isMe()) : ?>
                        <a href="" title="<?= _('Als Index-Server aktivieren/deaktivieren') ?>" class="<?= $host['index_server'] ? "checked" : "unchecked" ?>">
                            <?= Icon::create("checkbox-".($host['index_server'] ? "" : "un")."checked", Icon::ROLE_CLICKABLE)->asImg(20) ?>
                        </a>
                    <? else : ?>
                        <? if ($host['index_server']) : ?>
                            <a href=""
                               class="<?= $host['allowed_as_index_server'] ? "checked" : "unchecked" ?>"
                               title="<?= _('Diesen Server als Indexserver aktivieren. Suchanfragen werden immer auch an ihn gerichtet. Sie sollten nur einen Indexserver verwenden.') ?>">
                                <?= Icon::create("checkbox-".($host['allowed_as_index_server'] ? "" : "un")."checked", Icon::ROLE_CLICKABLE)->asImg(20) ?>
                            </a>
                        <? else : ?>
                            <?= Icon::create("checkbox-unchecked", Icon::ROLE_INACTIVE)->asImg(20, ['title' => _('Dieser Server steht nicht als Indexserver zur Verfügung.')]) ?>
                        <? endif ?>
                    <? endif ?>
                </td>
                <td class="active">
                    <? if ($host->isMe()) : ?>
                        <?= Icon::create("checkbox-checked", Icon::ROLE_INFO)->asImg(20) ?>
                    <? else : ?>
                        <a href=""
                           title="<?= _('Soll dieser Server und seine OERs für uns relevant sein?') ?>"
                           class="<?= $host['active'] ? "checked" : "unchecked" ?>">
                            <?= Icon::create("checkbox-".($host['active'] ? "" : "un")."checked", Icon::ROLE_CLICKABLE)->asImg(20) ?>
                        </a>
                    <? endif ?>
                </td>
                <td class="actions">
                    <? if (!$host->isMe()) : ?>
                        <a href="<?= $controller->link_for("oer/admin/ask_for_hosts/".$host->getId()) ?>"
                           title="<?= _('Diesen Server nach weiteren bekannten Hosts fragen.') ?>">
                            <?= Icon::create("campusnavi", Icon::ROLE_CLICKABLE)->asImg(20, ['width' => "20px", 'class' => "text-bottom"]) ?>
                        </a>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>

<? if (!$federation_problems && count($hosts) < 2 && !$_SESSION['Lernmarktplatz_no_thanx']) : ?>
    <div id="init_first_hosts_dialog" style="display: none;">
        <form action="<?= $controller->link_for("oer/admin/add_new_host") ?>" method="post">
            <h2><?= _('Werden Sie Teil des weltweiten Stud.IP Lernmarktplatzes!') ?></h2>
            <div>
                <?= _('Der Lernmarktplatz ist ein Ort des Austauschs von kostenlosen und freien Lernmaterialien. Daher wäre es schade, wenn er nur auf Ihr einzelnes Stud.IP beschränkt wäre. Der Lernmarktplatz ist daher als dezentrales Netzwerk konzipiert, bei dem alle Nutzer aller Stud.IPs sich gegenseitig Lernmaterialien tauschen können und nach Lernmaterialien anderer Nutzer suchen können. <em>Dezentral</em> heißt dieses Netzwerk, weil es nicht einen einzigen zentralen Server gibt, der wie eine große Suchmaschine alle Informationen bereit hält. Stattdessen sind im besten Fall alle Stud.IPs mit allen anderen Stud.IPs direkt vernetzt. So ein dezentrales Netz ist sehr ausfallsicher und es passt zur Opensource-Idee von Stud.IP, weil man sich von keiner zentralen Institution abhängig macht. Aber Ihr Stud.IP muss irgendwo einen ersten Kontakt zum großen Netzwerk aller Lernmarktplätze finden, um loslegen zu können. Wählen Sie dazu irgendeinen der unten aufgeführten Server aus. Sie werden Index-Server genannt und bilden das Tor zum Rest des ganzen Netzwerks. Achten Sie darauf, dass Sie mit mindestens einem, aber auch nicht zu vielen Indexservern verbunden sind.') ?>
            </div>

            <ul class="clean" style="text-align: center;">
                <li>
                    <?= \Studip\Button::create(_('Stud.IP Entwicklungsserver'), 'url', ['value' => 'https://develop.studip.de/studip/dispatch.php/oer/endpoints/']) ?>
                </li>
                <li>
                    <?= \Studip\Button::create(_('Nein, danke!'), 'nothanx') ?>
                </li>
            </ul>

        </form>
    </div>
    <script>
        jQuery(function () {
            jQuery('#init_first_hosts_dialog').dialog({
                'modal': true,
                'title': '<?= _('Index-Server hinzufügen') ?>',
                'width': "80%"
            });
        });
    </script>
<? endif ?>

<?
$actions = new ActionsWidget();
$actions->addLink(
    _('Server hinzufügen'),
    $controller->url_for("oer/admin/add_new_host"),
    Icon::create("add", Icon::ROLE_CLICKABLE),
    ['data-dialog' => "1"]
);
$actions->addLink(
    _('Serverdaten aktualisieren'),
    $controller->url_for("oer/admin/refresh_hosts"),
    Icon::create("refresh", Icon::ROLE_CLICKABLE)
);

Sidebar::Get()->addWidget($actions);
