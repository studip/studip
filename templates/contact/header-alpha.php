<?
    $tooltip = function ($size) {
        if (!$size) {
            $tip = _('Keine Einträge');
        } else {
            $template = ngettext(
                '%d Eintrag',
                '%d Einträge',
                $size
            );
            $tip = sprintf($template, $size);
        }
        return tooltip($tip, false);
    }
?>
<table class="contact-header" width="70%">
    <colgroup>
        <col width="9%">
    <? for ($i = 0; $i < 26; $i++): ?>
        <col width="3.5%">
    <? endfor; ?>
    </colgroup>
    <tbody>
        <tr>
            <td nobreak class="<? if (($filter ?: 'all') == 'all') echo 'active'; ?>"
                <?= $tooltip($size_of_book) ?>
            >
                <a href="<?= URLHelper::getLink('?filter=all') ?>">a-z</a>
            </td>
        <? for ($i = 0, $chr = 'a'; $i++ < 26; $chr++): ?>
            <td nobreak align="center" class="<? if ($filter == $chr) echo 'active'; ?><? if (!$sizes[$chr]) echo ' empty'; ?>"
                <?= $tooltip($sizes[$chr]) ?>
            >
                <a href="<?= URLHelper::getLink('', ['view' => $view, 'filter' => $chr]) ?>">
                    <?= $chr ?>
                </a>
            </td>
        <? endfor; ?>
        </tr>
    </tbody>
</table>
