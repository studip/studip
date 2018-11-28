<?php use Studip\Button, Studip\LinkButton; ?>
<? if ($is_inst) : ?>
    <h2><?= _('Lernmodule der Einrichtung') ?></h2>
<? else : ?> 
    <h2><?= _('Lernmodule der Veranstaltung') ?></h2>
<? endif ?>
<? if ($elearning_active) : ?>
    <? if ($new_account) : ?>
        <?=ELearningUtils::getNewAccountForm($new_account)?>
    <? else : ?>
        <? if (!count($content_modules) AND count($course_output['courses'])) : ?>
            <br>
            <div class="messagebox messagebox_info" style="background-image: none; padding-left: 15px">
                <?=$course_output['text']?><br>
                <? foreach ($course_output['courses'] as $course) : ?>        
                    <a href="<?=$course['url']?>"><?=sprintf(_('Kurs in %s'), $course['cms_name'])?></a>
                    <br>
                <? endforeach ?>
            </div>
        <? elseif (count($content_modules)) : ?>
            <?foreach ($content_modules as $module) : ?>        
                <? if ($module['show_header']) : ?>
                    <?=ELearningUtils::getModuleHeader(_("Angebundene Lernmodule"))?>
                <? endif ?>
                <?=$module['module']?>
                <br>
            <? endforeach ?>
            <br>
            <? if (count($course_output['courses'])) : ?>
                <?=$course_output['text']?><br>
                <? foreach ($course_output['courses'] as $course) : ?>        
                    <a href="<?=$course['url']?>"><?=sprintf(_('Kurs in %s'), $course['cms_name'])?></a>
                    <br>
                <? endforeach ?>
            <? endif ?>
        <? endif ?>
    <? endif ?>
<? endif ?>