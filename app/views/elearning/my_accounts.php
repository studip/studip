<?php use Studip\Button, Studip\LinkButton; ?>
    <h2><?=_('Meine Lernmodule und Benutzer-Accounts')?></h2>
    <? foreach($cms_list as $cms_index => $cms_data) : ?>
        <? if ($cms_anker_target == $cms_index) : ?>
            <a name='anker'></a>
        <? endif?>
        <?=ELearningUtils::getCMSHeader($cms_data['name'])?>
        <br>
        <?=ELearningUtils::getHeader(_("Mein Benutzeraccount"))?>
        <? if ($cms_data['account_form']) : ?>
            <?=$cms_data['account_form']?>
        <? else : ?>
            <? if ($cms_data['show_account_form'] AND $cms_data['user']) : ?>
                <?=ELearningUtils::getMyAccountForm('', $cms_index)?>
            <? elseif ($cms_data['show_account_form']) : ?>
                <?=ELearningUtils::getMyAccountForm(sprintf(_("Sie haben im System %s bisher keinen Benutzer-Account."), htmlReady($cms_data['name'])), $cms_index)?>
            <? endif ?>
            <? if ($cms_data['user'] AND $cms_data['start_link']) : ?>
                <div class="messagebox messagebox_info" style="background-image: none; padding-left: 15px">
                    <?=_('Hier gelangen Sie direkt zur Startseite im angebundenen System:')?>
                    <a href="<?= URLHelper::getScriptLink($cms_data['start_link']) ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlReady($cms_data['name']) ?>
                    </a>
                </div>
                <br>
            <? endif ?>
            <?=ELearningUtils::getHeader(_('Meine Lernmodule'))?>
            <? if (count($cms_data['modules'])) : ?>
                <? foreach ($cms_data['modules'] as $module_html) : ?>
                    <?=$module_html?>
                <? endforeach ?>
            <? else : ?>
                <table border="0" cellspacing="0" cellpadding="6">
                    <tr>
                        <td>
                            <?=sprintf(_("Sie haben im System %s keine eigenen Lernmodule."), htmlReady($cms_data['name']))?><br>
                            <br>
                        </td>
                    </tr>
                </table>
            <? endif ?>
            <br>
            <br>
            <?=$cms_data['new_module_form']?>
        <? endif ?>
        <?=ELearningUtils::getCMSFooter($cms_data['logo'])?>
        <br>
    <? endforeach ?>
