<? $now = mktime(12, 0, 0, date('n', time()), date('j', time()), date('Y', time())); ?>
<table class="blank">
    <tr>
        <td style="text-align: center;">
            <table style="width: 100%;">
                <tr>
                    <td colspan="8" style="vertical-align: top; text-align: center; white-space:nowrap;">
                        <div style="float:left; width:15%;">
                        <? if ($mod == 'NONAVARROWS') : ?>
                            &nbsp;
                        <? else : ?>
                            <a href="<?= $controller->url_for($href, ['imt' => mktime(12, 0, 0, date('n', $imt), 1, date('Y', $imt) - 1)]) ?>">
                               <?= Icon::create('arr_2left', 'clickable', ['title' => _('Ein Jahr zurück')])->asImg() ?>
                            </a>
                            <a href="<?= $controller->url_for($href, ['imt' => mktime(12, 0, 0, date('n', $imt) - 1, 1, date('Y', $imt))]) ?>">
                                <?= Icon::create('arr_1left', 'clickable', ['title' => _('Einen Monat zurück')])->asImg() ?>
                            </a>
                        <? endif; ?>
                        </div>
                        <div class="precol1w" style="float:left; text-align:center; width:70%;">
                            <?= sprintf("%s %s\n", strftime('%B', $imt), date('Y', $imt)) ?>
                        </div>
                        <div style="float:right; width:15%;">
                        <? if ($mod == 'NONAVARROWS') : ?>
                            &nbsp;
                        <? else : ?>
                            <a href="<?= $controller->url_for($href, ['imt' => mktime(12, 0, 0, date('n', $imt) + 1, 1, date('Y', $imt))]) ?>">
                                <?= Icon::create('arr_1right', 'clickable', ['title' => _('Einen Monat vor')])->asImg() ?>
                            </a>
                            <a href="<?= $controller->url_for($href, ['imt' => mktime(12, 0, 0, date('n', $imt), 1, date('Y', $imt) + 1)]) ?>">
                                <?= Icon::create('arr_2right', 'clickable', ['title' => _('Ein Jahr vor')])->asImg() ?>
                            </a>
                        <? endif; ?>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="blank">
            <table class="blank">
                <tr>
                    <? $week_days = [39092400, 39178800, 39265200, 39351600, 39438000, 39524400, 39610800]; ?>
                    <? foreach ($week_days as $week_day) : ?>
                    <td align="center" class="precol2w" width="25">
                        <?= strftime('%a', $week_day) ?>
                    </td>
                    <? endforeach; ?>
                    <td class="precol2w" width="25"> </td>
                </tr>
            <? $adow = date('w', mktime(12, 0, 0, date('n', $imt), 1, date('Y', $imt))); ?>
            <? if ($adow == 0) : ?>
                <? $adow = 6; ?>
            <? else : ?>
                <? $adow--; ?>
            <? endif; ?>
            <? $first_day = mktime(12, 0, 0, date('n', $imt), 1, date('Y', $imt)) - $adow * 86400; ?>
            <? $cor = 0; ?>
            <? if (date('n', $imt) == 3) : ?>
                <? $cor = 1; ?>
            <? endif; ?>
            <? $last_day = ((42 - ($adow + date('t', mktime(12, 0, 0, date('n', $imt), 1, date('Y', $imt))))) % 7 + $cor) * 86400
            + mktime(12, 0, 0, date('n', $imt), date('t', $imt), date('Y', $imt)); ?>
            <? for ($i = $first_day, $j = 0; $i <= $last_day; $i += 86400, $j++) : ?>
                <?
                $aday = date('j', $i);
                $style = '';
                if (($aday - $j - 1 > 0) || ($j - $aday > 6)) {
                    $style = 'light';
                }
                $hday = holiday($i);
                if ($j % 7 == 0) {
                    $ret .= '<tr>';
                }
                ?>
                <? if (abs($now - $i) < 43199 && !($style == 'light')) : ?>
                    <td class="celltoday" align="center" width="25" height="25">
                <? elseif (date('m', $i) != date('n', $imt)) : ?>
                    <td class="lightmonth" align="center" width="25" height="25">
                <? else : ?>
                    <td class="month" align="center" width="25" height="25">
                <? endif; ?>
                <? $js_inc = ''; ?>
                <? if (is_array($js_include)) : ?>
                    <?
                    $js_inc = " onClick=\"{$js_include['function']}(";
                    if (sizeof($js_include['parameters'])) {
                        $js_inc .= implode(", ", $js_include['parameters']) . ", ";
                    }
                    $js_inc .= "'" . date('m', $i) . "', '$aday', '" . date('Y', $i) . "')\"";
                    ?>
                <? endif; ?>
                <? if (abs($atime - $i) < 43199) : ?>
                    <? $aday = '<span class="current">'.$aday.'</span>' ?>
                <? endif; ?>
                <? if (($j + 1) % 7 == 0) : ?>
                    <a class="<?= $style ?>sday" href="<?= $controller->url_for($href, ['atime' => $i]) ?>" <?= $hday['name'] ? tooltip($hday['name']) : '' ?> <?= $js_inc ?>>
                        <?= $aday ?>
                    </a>
                </td>
                <td class="lightmonth" style="text-align: center; width: 25px; height: 25px;">
                    <a href="<?= $controller->url_for('calendar/single/week/', ['atime' => $i]) ?>">
                        <span class="kwmin"><?= strftime('%V', $i) ?></span>
                    </a>
                </td>
            </tr>
                <? else : ?>
                    <? switch ($hday['col']) {
                        case 1:
                            ?><a class="<?= $style ?>day" href="<?= $controller->url_for($href, ['atime' => $i]) ?>" <?= tooltip($hday['name']) . $js_inc ?>>
                               <?= $aday ?>
                            </a><?
                            break;
                        case 2:
                        case 3;
                            ?><a class="<?= $style ?>hday" href="<?= $controller->url_for($href, ['atime' => $i]) ?>" <?= tooltip($hday['name']) . $js_inc ?>>
                                <?= $aday ?>
                            </a><?
                            break;
                        default:
                            ?><a class="<?= $style ?>day" href="<?= $controller->url_for($href, ['atime' => $i]) ?>" <?= $js_inc ?>>
                                <?= $aday ?>
                            </a>
                    <?}?>
                    </td>
                <? endif; ?>
            <? endfor; ?>
            </table>
        </td>
    </tr>
</table>