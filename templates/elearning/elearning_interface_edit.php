<?php use Studip\Button, Studip\LinkButton; ?>
<h2><?= _('Lernmodule hinzufügen / entfernen') ?></h2>
<? if ($elearning_active) : ?>
    <? if (!count($content_modules)) : ?>
        <? if (count($course_output['courses'])) : ?>
            <?=$course_output['text']?><br>
            <? foreach ($course_output['courses'] as $course) : ?>
                <a href="<?=$course['url']?>"><?=sprintf(_('Kurs in %s'), $course['cms_name'])?></a>
                <br>
            <? endforeach ?>
        <? endif ?>
    <? else : ?>
        <?foreach ($content_modules as $module) : ?>
            <? if ($module['show_header']) : ?>
                <?=ELearningUtils::getModuleHeader(_("Angebundene Lernmodule"))?>
            <? endif ?>
            <?=$module['module']?>
            <br>
        <? endforeach ?>
    <? endif ?>
    <br>
    <? if ($cms_select AND ! count($user_modules)) : ?>
        <? if (count($course_output['courses'])) : ?>
            <?=$course_output['text']?><br>
            <? foreach ($course_output['courses'] as $course) : ?>
                <a href="<?=$course['url']?>"><?=sprintf(_('Kurs in %s'), $course['cms_name'])?></a>
                <br>
            <? endforeach ?>
        <? endif ?>
    <? endif ?>
    <? if ($cms_select) : ?>
        <br>
        <?=ELearningUtils::getCMSHeader($cms_name)?>
        <br>
        <? if (count($user_modules)) : ?>
            <?=ELearningUtils::getModuleHeader(sprintf(_("Ihre Lernmodule in %s"), $cms_name))?>
            <?foreach ($user_modules as $module) : ?>
                <?=$module['module']?>
                <br>
            <? endforeach ?>
        <? endif ?>
        <? if ($show_search) : ?>
            <br>
            <? if ($anker_target == "search") : ?>
                <a name='anker'></a>
            <? endif ?>
            <?=ELearningUtils::getHeader(_("Suche")) ?>
            <?=ELearningUtils::getSearchfield(
                        sprintf(_("Um im System %s nach Lernmodulen zu suchen, geben Sie einen Suchbegriff ein:"),
                        $cms_name))?>
            <br>
            <? if (count($search_modules)) : ?>
                <?=ELearningUtils::getHeader( sprintf( _("Gefundene Lernmodule zum Suchbegriff \"%s\""), htmlReady($search_key) ))?>
                <? foreach ($search_modules as $module) : ?>
                    <?=$module['module']?>
                    <br>
                <? endforeach ?>
                <br>
            <? elseif (mb_strlen( trim($search_key) ) > 2) : ?>
                <br>
                <b><font size="-1"><?=sprintf( _("Es gibt im System %s zu diesem Suchbegriff keine Lernmodule."),  $cms_name)?></font></b><br>
                <br>
            <? endif ?>
        <? else : ?>
            <br>
            <div class="messagebox messagebox_info" style="background-image: none; padding-left: 15px">
                <?=sprintf(_('Sie können im System %s nicht suchen, da Sie bisher keinen Benutzer-Account angelegt haben.'),
                           $cms_name)?><br>
                <a href="<?=URLHelper::getLink('dispatch.php/elearning/my_accounts')?>">
                <?=_('Jetzt einen Account erstellen.')?><br>
                </a>
            </div>
        <? endif ?>
        <? if ($show_ilias_empty_course) : ?>
            <form method="POST" action="<?=URLHelper::getLink() . "#anker"?>">
            <?=CSRFProtection::tokenTag()?>
            <?=ELearningUtils::getHeader(_("Leeren Kurs anlegen"))?>
            <div align="center">
            <br>
            <?=_('Hier können Sie einen leeren Ilias-Kurs für diese Veranstaltung anlegen. Die Teilnehmenden '
                .'der Veranstaltung können dann den Kurs betreten, auch wenn noch keine Lernmodule zugeordnet sind. '
                .'Solange der Kurs leer ist, erscheint auf der Seite "Meine Veranstaltungen und Einrichtungen" kein '
                .'Lernmodulsymbol für diese Veranstaltung. <b>Dieser Schritt kann nicht rückgängig gemacht werden.</b>')?>
            <br>
            <br>
            <input type="HIDDEN" name="anker_target" value="search">
            <input type="HIDDEN" name="view" value="<?=$view?>">
            <input type="HIDDEN" name="cms_select" value="<?=$cms_select?>">
            <?=Button::create(_('Anlegen'), 'create_course')?>
            <br>
            <br>
            </div>
            </form>
        <? endif ?>
        <? if (count($existing_courses)) : ?>
            <form method="POST" action="<?=URLHelper::getLink() . "#anker"?>>
            <?=CSRFProtection::tokenTag()?>
            <?=ELearningUtils::getHeader(_("Verknüpfung mit einem bestehenden Kurs"))?>
            <div align="center">
            <br>
            <?_('Wenn Sie die Veranstaltung mit einem bestehenden Ilias-Kurs verbinden wollen, wählen Sie hier '
               .'die Stud.IP-Veranstaltung, mit der der bestehende Kurs verknüpft ist. Beide Stud.IP-Veranstaltungen '
               .'sind dann mit dem selben Ilias-Kurs verknüpft. <b>Dieser Schritt kann nicht rückgängig gemacht werden.</b>')?>
            <br>
            <br>
            <select name="connect_course_sem_id" size="1">
                <option value="">
                    <?=_("Bitte auswählen")?>
                </option>
                <? foreach ($existing_courses as $key => $name) : ?>
                <option value="<?=$key?>">
                    <?=$name?>
                </option>
                <? endforeach ?>
            </select>
            <input type="HIDDEN" name="anker_target" value="search">
            <input type="HIDDEN" name="view" value="<?=$view?>">
            <input type="HIDDEN" name="cms_select" value="<?=$cms_select?>">
            <?=Button::create(_('Auswählen'), 'connect_course')?>
            <br>
            </div>
            </form>
            <br>
        <? endif ?>
        <? if ($show_ilias_link_info) : ?>
            <br>
            <?=ELearningUtils::getHeader(_("Links zu anderen ILIAS-Objekten"))?>
            <div align="center">
            <br>
            <?=_('Sie können beliebige weitere Objekte hinzufügen, indem Sie im verknüpften Kurs in ILIAS einen '
                .'internen Link zu den entsprechenden Objekten anlegen. '
                .'Wechseln Sie dazu in den Kurs, wählen Sie unter "Neues Objekt hinzufügen" die Option Weblink und legen '
                .'einen Link innerhalb von ILIAS an. Kehren Sie anschließend auf diese Seite zurück und klicken Sie in der Infobox '
                .'auf "Aktualisieren". Für die auf diese Weise verlinkten Objekte müssen Sie selbst sicherstellen, dass die Teilnehmenden '
                .'des Kurses Zugriff darauf haben.')?>
            <br>
            <br>
            </div>
        <? endif ?>
        <?=ELearningUtils::getCMSFooter($cms_logo)?>
        <br>
        <? if ($anker_target == "choose") : ?>
            <a name='anker'></a>
        <? endif ?>
    <? endif ?>
    <?=ELearningUtils::getCMSSelectbox(_("Um Lernmodule hinzuzufügen, wählen Sie ein angebundenes System aus:"))?>
<? endif?>