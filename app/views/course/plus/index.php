<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
use Studip\Button, Studip\LinkButton;

?>

<? if ($_SESSION['admin_modules_data']["orig_bin"] != $_SESSION['admin_modules_data']["changed_bin"]): ?>
    <?= MessageBox::info(_("Diese Daten sind noch nicht gespeichert.")) ?>
<? endif; ?>

<form action="<?= URLHelper::getLink($save_url) ?>" method="post" class="default">
<?= CSRFProtection::tokenTag() ?>
<input name="uebernehmen" value="1" type="hidden">
<table class="default nohover plus">
<!-- <caption><?=_("Inhaltselemente")?></caption> -->
<tbody>
<?
foreach ($available_modules as $category => $pluginlist) {
    $visibility = "";
    if ($_SESSION['plus']['displaystyle'] != 'category' && $category != 'Funktionen von A-Z') {
        $visibility = "invisible";
    }
    if (isset($_SESSION['plus']) && !$_SESSION['plus']['Kategorie'][$category] && $category != 'Funktionen von A-Z') {
        $visibility = "invisible";
    }

    ?>
    <tr class="<?= $visibility; ?>">
        <th colspan=3>
            <?= htmlReady($category) ?>
        </th>
    </tr>

    <? foreach ($pluginlist as $key => $val) {

        if ($val['type'] == 'plugin') {
            $plugin = $val['object'];
            $plugin_activated = $plugin->isActivated();
            $info = $plugin->getMetadata();

            //Checkbox
            $anchor = 'p_' . $plugin->getPluginId();
            $cb_name = 'plugin_' . $plugin->getPluginId();
            $cb_disabled = '';
            $cb_checked = $plugin_activated ? "checked" : "";

            $pluginname = isset($info['displayname']) ? $info['displayname'] : $plugin->getPluginname();
            $URL = $plugin->getPluginURL();

        } elseif ($val['type'] == 'modul') {

            $modul = $val['object'];

            $pre_check = null;
            if (isset($modul['preconditions'])) {
                $method = 'module' . $val['modulkey'] . 'Preconditions';
                if (method_exists($modules, $method)) {
                    $pre_check = $modules->$method($_SESSION['admin_modules_data']["range_id"], $modul['preconditions']);
                }
            }

            $anchor = 'm_' . $modul['id'];
            $cb_name = $val['modulkey'] . '_value';
            $cb_disabled = $pre_check ? 'disabled' : '';
            $cb_checked = $modules->isBit($_SESSION['admin_modules_data']["changed_bin"], $modul["id"]) ? "checked" : "";



            $URL = $GLOBALS['ASSETS_URL'].'images';

            if ($sem_class) {
                $studip_module = $sem_class->getModule($sem_class->getSlotModule($val['modulkey']));
            }

            $info = ($studip_module instanceOf StudipModule) ? $studip_module->getMetadata() : ($modul['metadata'] ? $modul['metadata'] : []);
            $pluginname = isset($info['displayname']) ? $info['displayname'] : $modul['name'];
            $getModuleXxExistingItems = "getModule" . $val['modulkey'] . "ExistingItems";

        }
        //if(isset($info['complexity']) && isset($_SESSION['plus']) && !$_SESSION['plus']['Komplex'][$info['complexity']])continue;
        ?>

        <tr id="<?= htmlReady($anchor);?>" class="<?= $visibility; ?> <?= $pre_check != null ? ' quiet' : '' ?>">
            <td class="element" colspan=3>

                <div class="plus_basic">

                    <!-- checkbox -->
                    <input type="checkbox" id="<?= $pluginname ?>" name="<?= $cb_name ?>" value="TRUE" <?= $cb_disabled ?> <?= $cb_checked ?>
                    onClick="form.submit()">

                    <div class="element_header">

                        <!-- Name -->
                        <label for="<?= $pluginname ?>"><strong><?= htmlReady($pluginname) ?></strong>
                        <? if ($val['type'] == 'modul' && $sem_class && is_a($studip_module, "StandardPlugin")) : ?>
                            <? $already_displayed_plugins[] = $mod ?>
                            (<?= htmlReady($studip_module->getPluginName()) ?>)
                        <? endif ?>
                        </label>
                        <!-- komplex -->
                        <?/* switch ($info['complexity']) {
                            case 3:
                                $complexname = 'Intensiv';
                                break;
                            case 2:
                                $complexname = 'Erweitert';
                                break;
                            case 1:
                                $complexname = 'Standard';
                                break;
                            default:
                                $complexname = 'Nicht angegeben';
                                break;
                        }

                        if (isset($info['complexity'])) {

                            $color1 = isset($info['complexity']) ? "hsl(57, 100%, 50%)" : "hsl(0, 0%, 100%)";
                            $color2 = isset($info['complexity']) && $info['complexity'] > 1 ? "hsl(42, 100%, 50%)" : "hsl(0, 0%, 100%)";
                            $color3 = isset($info['complexity']) && $info['complexity'] > 2 ? "hsl(15, 100%, 50%)" : "hsl(0, 0%, 100%)";
                            $border_color1 = isset($info['complexity']) ? "hsl(57, 100%, 45%)" : "hsl(0, 0%, 80%)";
                            $border_color2 = isset($info['complexity']) && $info['complexity'] > 1 ? "hsl(42, 100%, 45%)" : "hsl(0, 0%, 80%)";
                            $border_color3 = isset($info['complexity']) && $info['complexity'] > 2 ? "hsl( 15, 100%, 45%)" : "hsl(0, 0%, 80%)";

                            ?>
                            <div class="complexity" title="Komplexität: <?= $complexname ?>">
                                <div class="complexity_element"
                                     style="background-color: <?= $color1 ?>; border-color: <?= $border_color1 ?>;"></div>
                                <div class="complexity_element"
                                     style="background-color: <?= $color2 ?>; border-color: <?= $border_color2 ?>;"></div>
                                <div class="complexity_element"
                                     style="background-color: <?= $color3 ?>; border-color: <?= $border_color3 ?>;"></div>
                            </div>
                        <? } */?>

                    </div>

                    <div class="element_description">

                        <!-- icon -->
                        <? if (isset($info['icon'])) : ?>
                            <? /* TODO: Plugins should use class "Icon"  */ ?>
                            <? if (is_string($info['icon'])) : ?>
                                <img class="plugin_icon text-bottom" alt="" src="<?= $URL . "/" . $info['icon'] ?> ">
                            <? else: ?>
                                <?= $info['icon']->asImg(['class' => 'plugin_icon text-bottom', 'alt' => '']) ?>
                            <? endif ?>
                        <? endif ?>

                        <!-- shortdesc -->
                        <strong class="shortdesc">
                            <? if (isset($info['descriptionshort'])) : ?>
                                <? foreach (explode('\n', $info['descriptionshort']) as $descriptionshort) { ?>
                                    <?= htmlReady($descriptionshort) ?>
                                <? } ?>
                            <? endif ?>
                            <? if (!isset($info['descriptionshort'])) : ?>
                                <? if (isset($info['summary'])) : ?>
                                    <?= htmlReady($info['summary']) ?>
                                <? elseif (isset($info['description'])) : ?>
                                    <?= htmlReady($info['description']) ?>
                                <? else: ?>
                                    <?= _("Keine Beschreibung vorhanden.") ?>
                                <? endif ?>
                            <? endif ?>
                        </strong>

                    </div>

                    <!-- inhaltlöschenbutton -->
                    <? if ($val['type'] == 'plugin' && method_exists($plugin, 'deleteContent')) echo LinkButton::create(_('Inhalte löschen'), URLHelper::getURL("?deleteContent=true&name=" . $key), ['style' => 'float:right; z-index: 1;']); ?>
                    <? if ($val['type'] == 'modul' && $studip_module instanceOf StudipModule && method_exists($studip_module, 'deleteContent')) echo LinkButton::create(_('Inhalte löschen'), URLHelper::getURL("?deleteContent=true&name=" . $key), ['style' => 'float:right; z-index: 1;']); ?>

                </div>

                <? if ($_SESSION['plus']['View'] == 'openall' || !isset($_SESSION['plus'])) { ?>

                    <div class="plus_expert hidden-tiny-down">

                        <div class="screenshot_holder">
                            <? if (isset($info['screenshot']) || isset($info['screenshots'])) :
                                if(isset($info['screenshots'])){
                                    $title = $info['screenshots']['pictures'][0]['title'];
                                    $source = $info['screenshots']['path'].'/'.$info['screenshots']['pictures'][0]['source'];
                                } else {
                                    $fileext = end(explode(".", $info['screenshot']));
                                    $title = str_replace("_"," ",basename($info['screenshot'], ".".$fileext));
                                    $source = $info['screenshot'];
                                }
                                ?>

                                <a href="<?= $URL . "/" . $source ?>"
                                   data-lightbox="<?= $pluginname ?>" data-title="<?= $title ?>">
                                    <img class="big_thumb" src="<?= $URL . "/" . $source ?>"
                                         alt="<?= $pluginname ?>"/>
                                </a>

                                <?
                                if (isset($info['additionalscreenshots']) || (isset($info['screenshots']) && count($info['screenshots']) > 1) ) {
                                    ?>

                                    <div class="thumb_holder">
                                    <?  if (isset($info['screenshots'])){
                                            $counter = count($info['screenshots']['pictures']);
                                            $cstart = 1;
                                        } else {
                                            $counter = count($info['additionalscreenshots']);
                                            $cstart = 0;
                                        } ?>

                                        <? for ($i = $cstart; $i < $counter; $i++) {

                                            if (isset($info['screenshots'])){
                                                $title = $info['screenshots']['pictures'][$i]['title'];
                                                $source = $info['screenshots']['path'].'/'.$info['screenshots']['pictures'][$i]['source'];
                                            } else {
                                                $fileext = end(explode(".", $info['additionalscreenshots'][$i]));
                                                $title = str_replace("_"," ",basename($info['additionalscreenshots'][$i], ".".$fileext));
                                                $source = $info['additionalscreenshots'][$i];
                                            }

                                             ?>

                                            <a href="<?= $URL . "/" . $source ?>"
                                               data-lightbox="<?= $pluginname ?>"
                                               data-title="<?= $title ?>">
                                                <img class="small_thumb"
                                                     src="<?= $URL . "/" . $source ?>"
                                                     alt="<?= $pluginname ?>"/>
                                            </a>

                                        <? } ?>

                                    </div>

                                <? } ?>

                            <? endif ?>
                        </div>

                        <div class="descriptionbox">

                            <!-- inhaltlöschenbutton -->
                            <?// if ($val['type'] == 'plugin' && method_exists($plugin, 'deleteContent')) echo LinkButton::create(_('Inhalte löschen'), URLHelper::getURL("?deleteContent=true&name=" . $key), array('style' => 'float:right; z-index: 1;')); ?>
                            <?// if ($val['type'] == 'modul' && $studip_module instanceOf StudipModule && method_exists($studip_module, 'deleteContent')) echo LinkButton::create(_('Inhalte löschen'), URLHelper::getURL("?deleteContent=true&name=" . $key), array('style' => 'float:right; z-index: 1;')); ?>

                            <!-- tags -->
                            <? if (isset($info['keywords'])) : ?>
                                <ul class="keywords">
                                    <? foreach (explode(';', $info['keywords']) as $keyword) {
                                        echo '<li>' . htmlReady($keyword) . '</li>';
                                    }?>
                                </ul>
                            <? endif ?>

                            <!-- longdesc -->
                            <? if (isset($info['descriptionlong'])) : ?>
                            <? foreach (explode('\n', $info['descriptionlong']) as $descriptionlong) { ?>
                                <p class="longdesc">
                                    <?= htmlReady($descriptionlong) ?>
                                </p>
                            <? } ?>
                            <? endif ?>

                            <? if (!isset($info['descriptionlong']) && isset($info['summary'])) : ?>
                                <p class="longdesc">
                                    <? if (isset($info['description'])) : ?>
                                        <?= htmlReady($info['description']) ?>
                                    <? else: ?>
                                        <?= _("Keine Beschreibung vorhanden.") ?>
                                    <? endif ?>
                                </p>
                            <? endif ?>

                            <? if ($val['type'] == 'modul') {
                                $getModuleXxExistingItems = "getModule" . $val['modulkey'] . "ExistingItems";

                                if (method_exists($modules, $getModuleXxExistingItems)) {
                                    if ($modules->$getModuleXxExistingItems($_SESSION['admin_modules_data']["range_id"]) &&
                                        $_SESSION['admin_modules_data']["modules_list"][$val['modulkey']] && $registered_modules[$val['modulkey']]["msg_pre_warning"]
                                    )
                                        printf('<p><strong>' . _('Hinweis') . ':</strong> ' . $registered_modules[$val['modulkey']]["msg_pre_warning"] . '</p>',
                                            $modules->$getModuleXxExistingItems($_SESSION['admin_modules_data']["range_id"]));
                                }
                            }
                            ?>

                            <? if (isset($info['homepage'])) : ?>
                                <p>
                                    <strong><?= _('Weitere Informationen:') ?></strong>
                                    <a href="<?= htmlReady($info['homepage']) ?>"><?= htmlReady($info['homepage']) ?></a>
                                </p>
                            <? endif ?>

                            <!-- helplink -->
                            <? if (isset($info['helplink'])) : ?>
                                <a class="helplink" href=" <?= htmlReady($info['helplink']) ?> ">...mehr</a>
                            <? endif ?>

                        </div>
                    </div>
                <? } ?>
            </td>
        </tr>
    <?
    }
} ?>
</tbody>

<tfoot>
<tr class="hidden-js">
    <td align="center" colspan="3">
        <?= Button::create(_('An- / Ausschalten'), 'uebernehmen') ?>
    </td>
</tr>
</tfoot>
</table>
</form>
