<? foreach ($votes as $vote): ?>
    <tr>
        <td>
            <?= htmlReady($vote->title) ?>
        </td>
        <td>
            <?= ObjectdisplayHelper::link($vote->author) ?>
        </td>
        <td>
            <?= strftime("%d.%m.%Y %T", $vote->startdate) ?>
        </td>
        <td>
            <? if ($vote->stopdate): ?>
                <?= strftime("%d.%m.%Y %T", $vote->stopdate) ?>
            <? else: ?>
                <? if ($vote->timespan): ?>
                    <?= strftime("%d.%m.%Y %T", $vote->startdate + $vote->timespan) ?>
                <? else: ?>
                    <?= _('Unbegrenzt') ?>
                <? endif; ?>
            <? endif; ?>
        </td>
        <td class="actions">
            <?= $this->render_partial("vote/_actions.php", array('vote' => $vote)) ?>
        </td>
    </tr>
<? endforeach; ?>