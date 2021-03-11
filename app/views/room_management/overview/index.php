<section class="overview-action-tile-container studip-widget-wrapper">
    <? if ($show_resource_actions): ?>
        <article class="overview-action-tile studip">
            <header class="widget-header"><h1><?= _('Raumplanung') ?></h1></header>
            <ul>
                <? if ($room_requests_activated) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink(
                                 'dispatch.php/resources/room_request/overview') ?>">
                            <?= _('Anfragenliste') ?>
                        </a>
                    </li>
                <? endif ?>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/room_planning/booking_plan') ?>">
                        <?= _('Belegungsplan') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/location/index') ?>">
                        <?= _('Struktur') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/room_management/planning/index') ?>">
                        <?= _('Gruppenbelegungspläne') ?>
                    </a>
                </li>
            </ul>
        </article>
    <? endif ?>

    <? if ($user_is_global_resource_admin): ?>
        <article class="overview-action-tile studip">
            <header class="widget-header"><h1><?= _('Export') ?></h1></header>
            <ul>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/export/select_booking_sources') ?>">
                        <?= _('Raumgruppen auswählen') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/export/select_booking_sources',
                             ['select_rooms' => '1']) ?>">
                        <?= _('Räume auswählen') ?>
                    </a>
                </li>
            </ul>
        </article>
        <article class="overview-action-tile studip">
            <header class="widget-header"><h1><?= _('Administration') ?></h1></header>
            <ul>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/admin/permissions/global') ?>">
                        <?= _('Globale Berechtigungen verwalten') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/admin/global_locks') ?>">
                        <?= _('Globale Sperren verwalten') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= URLHelper::getLink(
                             'dispatch.php/resources/admin/user_permissions') ?>">
                        <?= _('Ressourcen-Berechtigungen verwalten') ?>
                    </a>
                </li>
                <? if ($user_is_root) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink(
                                 'dispatch.php/resources/admin/configuration') ?>">
                            <?= _('Konfigurationsoptionen') ?>
                        </a>
                    </li>
                <? endif ?>
            </ul>
        </article>
    <? endif ?>
</section>
<? if ($room_requests && count($room_requests)) : ?>
<br>
    <table class="default request-list">
        <caption><?= _('Aktuelle Raumanfragen') ?></caption>
        <thead>
                <tr>
                    <th>
                        <?= Icon::create('radiobutton-checked')->asImg(
                            [
                                'class' => 'text-bottom',
                                'title' => _('Markierung')
                            ]
                        ) ?>
                    </th>
                    <th data-sort="text"><?= _('Nr.') ?></th>
                    <th data-sort="text"><?= _('Name') ?></th>
                    <th data-sort="text"><?= _('Lehrende Person(en)') ?></th>
                    <th data-sort="text"><?= _('Raum') ?></th>
                    <th data-sort="text"><?= _('Plätze') ?></th>
                    <th data-sort="text"><?= _('Anfragende Person') ?></th>
                    <th data-sort="htmldata"><?= _('Art') ?></th>
                    <th data-sort="htmldata"><?= _('Dringlichkeit') ?></th>
                    <th data-sort="num"><?= _('letzte Änderung') ?></th>
                    <th><?= _('Aktionen') ?></th>
                </tr>
            </thead>
        <tbody>
            <? foreach ($room_requests as $room_request): ?>
                <?= $this->render_partial(
                    'resources/_common/_request_tr',
                    ['request' => $room_request]
                )?>
            <? endforeach ?>
        </tbody>
    </table>
<? elseif ($display_current_requests) : ?>
    <?= MessageBox::info(
        _('Es liegen keine aktuellen Raumanfragen vor!')
    ) ?>
<? endif ?>