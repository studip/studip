<table class="widget-container-preview">
    <colgroup>
    <? for ($i = 0; $i < $width; $i += 1): ?>
        <col width="<?= round(100 / $width, 2) ?>%">
    <? endfor; ?>
    </colgroup>
    <tbody>
    <? foreach ($preview as $row): ?>
        <tr>
        <? foreach ($row as $item): ?>
            <td rowspan="<?= $item['height'] ?>" colspan="<?= $item['width'] ?>"
                class="<? if ($item['label'] === false) echo 'empty'; ?>"
            ><?= htmlReady($item['label']) ?></td>
        <? endforeach; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
