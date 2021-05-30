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

            $pluginname = $val['displayname'];
            $URL = $plugin->isCorePlugin() ? $GLOBALS['ABSOLUTE_URI_STUDIP'] : $plugin->getPluginURL();
            $pluginvisibility = $val['visibility'];
        }
        ?>

        <tr id="<?= htmlReady($anchor);?>" class="<?= $visibility; ?> <?= $pre_check != null ? ' quiet' : '' ?>">
            <td class="element" colspan=3>

                <div class="plus_basic">

                    <!-- checkbox -->
                    <input type="checkbox"
                           id="<?= $pluginname ?>"
                           name="<?= $cb_name ?>"
                           data-moduleclass="<?= htmlReady($val['moduleclass']) ?>"
                           data-key="<?= htmlReady($val['modulkey']) ?>"
                           value="TRUE" <?= $cb_disabled ?> <?= $cb_checked ?>
                           onClick="STUDIP.Plus.setModule.call(this);">

                    <div class="element_header">

                        <!-- Name -->
                        <label for="<?= $pluginname ?>">
                            <strong><?= htmlReady($pluginname) ?></strong>
                            <? if ($cb_checked) : ?>
                                <?=Icon::create(
                                    $pluginvisibility === 'autor' ? 'visibility-visible' : 'visibility-invisible',
                                    Icon::ROLE_INFO,
                                    ['title' => sprintf(_('%s für Studierende'), $pluginvisibility === 'autor' ? _('Sichtbar') : _('Unsichtbar'))]
                                )?>
                            <? endif ?>
                        </label>

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


                    <? if ($plugin_activated) : ?>
                        <?php
                            $actionMenu = ActionMenu::get();

                            $actionMenu->addLink(
                                $controller->url_for('/edittool/' . $key),
                                _('Optionen bearbeiten'),
                                Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
                                ['data-dialog' => 'size=auto']
                            );
                        if (method_exists($plugin, 'deleteContent')) {
                            $actionMenu->addLink(
                                $controller->url_for('/index', ['deleteContent' => 1, 'name' => $key]),
                                _('Inhalte löschen'),
                                Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20])
                            );
                        }
                        ?>
                        <div style="float: right">
                        <?= $actionMenu->render() ?>
                        </div>
                    <? endif ?>
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
