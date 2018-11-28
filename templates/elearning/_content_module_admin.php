<?php use Studip\Button, Studip\LinkButton; ?>
<? if ($module_anker_target) : ?>
    <a name='anker'></a>
<? endif ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
    <tr>
        <? printhead(0, 0, $module_link, $module_is_open ? 'open' : 'close', $module_is_new, $module_icon, '<a href="'.$module_link.'" class="tree">'.$module_title.'</a>', $module_source, $module_change_date) ?>
    </tr>
</table>
<? if ($module_is_open): ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <?= printcontent(0, 0, $module_description .($module_buttons ? '<br><br>'.$module_buttons : ''), ''); ?>
    </tr>
</table>
<? endif ?>