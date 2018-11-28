<?php
Helpbar::get()->addPlainText(
    _('Bedienungshinweise'),
    _('Sie befinden sich hier in der Administrationsansicht des Forums. '
    . 'Mit den blauen Pfeilen können Sie einen oder mehrere Einträge auswählen, welche dann verschoben werden können. '),
    Icon::create('info', 'info_alt')
);
Helpbar::get()->addPlainText(
    '',
    _('Sie sollten nicht mehr als 20 Einträge gleichzeitig auswählen, da das verschieben sonst sehr lange dauern kann.')
);
?>
<div id="forum">
    <ul style="margin: 0; padding-left: 20px;" class="js">
    <? foreach ($list as $category_id => $entries) : ?>
        <li data-id="<?= $category_id ?>">
            <a class="tooltip2"></a>
            <b><?= htmlReady($categories[$category_id]) ?></b>
            <a href="javascript:STUDIP.Forum.paste('<?= $category_id ?>');" data-role="paste" style="display: none">
                <?= Icon::create('arr_2left', 'sort')->asImg() ?>
            </a>    
            <br>

            <?= $this->render_partial('admin/childs', compact('entries')) ?>
        </li>
    <? endforeach ?>
    </ul>
</div>
<noscript>
    <?= MessageBox::error(_('Die Forenadministration funktioniert nur mit eingeschaltetem JavaScript!')) ?>
</noscript>