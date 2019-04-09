<?
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

?>
<table class="default">
    <caption>
        <?= sprintf(_('Veranstaltungen mit regelmäßigen Zeiten am %s, %s Uhr'), htmlReady($day), htmlReady($timespan)) ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Nummer') ?></th>
            <th><?= _('Name') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($seminars as $seminar) : ?>
            <tr class="<?= TextHelper::cycle('table_row_odd', 'table_row_even') ?>">
                <td width="15%"><?= htmlReady($seminar->getNumber()) ?></td>
                <td width="45%">
                    <a href="<?= URLHelper::getLink('dispatch.php/course/details/?sem_id='. $seminar->getId()) ?>">
                        <?= Icon::create('link-intern', 'clickable')->asImg() ?>
                        <?= htmlReady($seminar->getName()) ?>
                    </a>
                </td>
                <td width="40%" class="schedule-adminbind">
                    <? $cycles = CalendarScheduleModel::getSeminarCycleId($seminar, $start, $end, $day) ?>

                    <? foreach ($cycles as $cycle) : ?>
                        <span><?= $cycle->toString() ?></span>

                        <? $visible = CalendarScheduleModel::isSeminarVisible($seminar->getId(), $cycle->getMetadateId()) ?>

                        <?= LinkButton::create(
                                _('Ausblenden'),
                                $controller->url_for('calendar/schedule/adminbind/'. $seminar->getId() .'/'. $cycle->getMetadateId() .'/0'),
                                [
                                    'id'      => $seminar->getId() . '_' . $cycle->getMetadateId() . '_hide',
                                    'onclick' => "STUDIP.Schedule.instSemUnbind('" . $seminar->getId() . "','" . $cycle->getMetadateId() . "'); return false;",
                                    'style'   => ($visible ? '' : 'display: none')
                                ]) ?>

                        <?= LinkButton::create(
                                _('Einblenden'),
                                $controller->url_for('calendar/schedule/adminbind/'. $seminar->getId() .'/'. $cycle->getMetadateId() .'/1'),
                                [
                                    'id'      => $seminar->getId() . '_' . $cycle->getMetadateId() . '_show',
                                    'onclick' => "STUDIP.Schedule.instSemBind('" . $seminar->getId() . "','" . $cycle->getMetadateId() . "'); return false;",
                                    'style'   => ($visible ?  'display: none' : '')
                                ]) ?>
                        <br>
                    <? endforeach ?>
                </td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
<br>
