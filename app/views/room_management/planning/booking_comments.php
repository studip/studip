<? if ($data): ?>
    <table class="default" <?= $standalone ? 'style="border: 1px solid black; border-collapse: collapse;"' : '' ?>>
        <thead>
            <tr>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= htmlReady(
                        sprintf(
                            _('%d. Kalenderwoche'),
                            $date->format('W')
                        )
                    ) ?>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Montag') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week monday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Dienstag') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week tuesday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Mittwoch') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week wednesday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Donnerstag') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week thursday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Freitag') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week friday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Samstag') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week saturday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
                <th <?= $standalone ? 'style="border: 1px solid black; width: 12.5%;"' : '' ?>>
                    <?= _('Sonntag') ?>
                    <div>
                        <?= date(
                            'd.m.Y',
                            strtotime('this week sunday', $date->getTimestamp())
                        ) ?>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($data as $row): ?>
                <tr>
                    <? foreach ($row as $i => $cell): ?>
                        <? if ($i == 0): ?>
                            <td <?= $standalone
                                  ? 'style="border: 1px solid black; min-height: 60px; height: 60px;"'
                                  : '' ?>>
                                <?= htmlReady($cell) ?>
                            </td>
                        <? else: ?>
                            <td <?= $standalone
                                  ? 'style="border: 1px solid black; min-height: 60px; height: 60px;"'
                                  : '' ?>>
                                <? if ($cell): ?>
                                    <? foreach ($cell as $day_item): ?>
                                        <div <?= $standalone
                                               ? 'style="margin-bottom: 1em;"'
                                               : '' ?>>
                                            <div>
                                                <strong>
                                                    <?= htmlReady($day_item[0]) ?>
                                                </strong>
                                            </div>
                                            <?= htmlReady($day_item[1]) ?>
                                        </div>
                                    <? endforeach ?>
                                <? endif ?>
                            </td>
                        <? endif ?>
                    <? endforeach ?>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <? if ($standalone): ?>
        <p>
            <?= sprintf(
                _('In der %d. Kalenderwoche sind keine Buchungen mit Kommentaren vorhanden!'),
                $date->format('W')
            ) ?>
        </p>
    <? else: ?>
        <?= MessageBox::info(
            sprintf(
                _('In der %d. Kalenderwoche sind keine Buchungen mit Kommentaren vorhanden!'),
                $date->format('W')
            )
        ) ?>
    <? endif ?>
<? endif ?>
