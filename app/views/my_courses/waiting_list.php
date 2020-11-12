<? SkipLinks::addIndex(_("Wartelisten"), 'my_waitlists') ?>
<table class="default collapsable" id="my_waitlists">
    <caption>
        <?= _("Anmelde- und Wartelisteneinträge") ?>
    </caption>
    <colgroup class="hidden-small-down">
        <col width="1px">
        <col width="65%">
        <col width="7%">
        <col width="10%">
        <col width="10%">
        <col width="15%">
        <col width="3%">
    </colgroup>
    <colgroup class="hidden-medium-up">
        <col width="1px">
    </colgroup>

    <thead>
        <tr>
            <th></th>
            <th style="text-align: left"><?= _("Name") ?></th>
            <th class="hidden-small-down"><?= _('Inhalt') ?></th>
            <th style="text-align: center"><?= _("Datum") ?></th>
            <th class="hidden-small-down" style="text-wrap: none; white-space: nowrap"><b><?= _("Position/Chance") ?></th>
            <th class="hidden-small-down"><?= _("Art") ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($waiting_list as $wait) {

        // wir sind in einer Anmeldeliste und brauchen Prozentangaben
        if ($wait["status"] == "claiming") {
            // Grün der Farbe nimmt mit Wahrscheinlichkeit ab
            $chance_color = dechex(55 + $wait['admission_chance'] * 2);
        } // wir sind in einer Warteliste
        else {
            $chance_color = $wait["position"] < 30
                ? dechex(255 - $wait["position"] * 6)
                : 44;
        }

        $seminar_name = $wait["Name"];
        if (SeminarCategories::GetByTypeId($wait['sem_status'])->studygroup_mode) {
            $seminar_name .= ' (' . _("Studiengruppe") . ', ' . _("geschlossen") . ')';
        }
        ?>
        <tr>
            <td title="<?=_("Position oder Wahrscheinlichkeit")?>" style="background:#44<?= $chance_color ?>44">
            </td>

            <td>
                <a href="<?= URLHelper::getLink('dispatch.php/course/details/', ['sem_id' => $wait['seminar_id'], 'send_from_search_page' => 'dispatch.php/my_courses/index', 'send_from_search' => 'TRUE']) ?>">
                    <?= htmlReady($seminar_name) ?>
                </a>
                <?php if ($wait['status'] == 'claiming') : ?>
                    <br>
                    <?= sprintf(_('Priorität %1$u im Anmeldeset "%2$s"'), $wait['priority'], $wait['cname']) ?>
                <?php endif ?>
            </td>
            <td class="hidden-small-down">
                <a data-dialog="size=auto" href="<?= $controller->url_for(sprintf('course/details/index/%s', $wait['seminar_id'])) ?>">
                    <? $params = tooltip2(_("Veranstaltungsdetails anzeigen")); ?>
                    <? $params['style'] = 'cursor: pointer'; ?>
                    <?= Icon::create('info-circle', 'inactive')->asImg(20, $params) ?>
                </a>
            </td>
            <td style="text-align: center">
                <?= $wait["status"] == "claiming" ? date("d.m.", $wait["admission_endtime"]) : "-" ?>
            </td>

            <td class="hidden-small-down" style="text-align: center">
                <?= $wait["status"] == "claiming" ? ($wait['admission_chance'] . "%") : $wait["position"] ?>
            </td>

            <td class="hidden-small-down" style="wtext-align: center">
                <? if ($wait["status"] == "claiming") : ?>
                    <?= _("Autom.") ?>
                <? elseif ($wait["status"] == "accepted") : ?>
                    <?= _("Vorl.") ?>
                <?
                else : ?>
                    <?= _("Wartel.") ?>
                <? endif ?>
            </td>

            <td style="text-align: right">
                <? if ($wait["status"] == "accepted" && $wait['admission_binding']) : ?>
                    <a href="<?= $controller->url_for('my_courses/decline_binding') ?>">
                        <?= Icon::create('door-leave+decline', 'inactive', ['title' => _("Die Teilnahme ist bindend. Bitte wenden Sie sich an die Lehrenden.")])->asImg(20) ?>
                    </a>
                <?  else : ?>
                    <a href="<?= URLHelper::getLink(sprintf('dispatch.php/my_courses/decline/%s', $wait['seminar_id']), ['cmd' => 'suppose_to_kill_admission']) ?>">
                        <?= Icon::create('door-leave', 'inactive', ['title' => _("aus der Veranstaltung abmelden")])->asImg(20) ?>
                    </a>
                <? endif ?>
            </td>
        </tr>
    <? } ?>
    </tbody>
</table>
<br>
<br>
