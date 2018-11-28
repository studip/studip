<?php use Studip\Button, Studip\LinkButton;?>
<form action="<?= $controller->url_for('profilemodules/update', compact('username')) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input name="uebernehmen" value="1" type="hidden">
    <table class="default nohover plus" id="profile_modules">
        <!-- <caption><?=_("Inhaltselemente")?></caption> -->
        <tbody>

<?
    foreach ($sortedList as $category => $pluginlist) {
        $visibility = "";
        if ($_SESSION['profile_plus']['displaystyle'] != 'category' && $category != 'Funktionen von A-Z') {
            $visibility = "invisible";
        }
        if (isset($_SESSION['profile_plus']) && !$_SESSION['profile_plus']['Kategorie'][$category] && $category != 'Funktionen von A-Z') {
            $visibility = "invisible";
        }
?>
        <tr class="<?= $visibility; ?>">
            <th colspan = 3>
                <?= $category ?>
            </th>
        </tr>

    <?  foreach ($pluginlist as $key => $val) {

            $plugin=$val['object'];
            $info = $plugin->getMetadata();
            $pluginname = isset($info['displayname']) ? $info['displayname'] : $plugin->getPluginname();
            $URL = $plugin->getPluginURL();
            $anchor = 'p_' . $plugin->getPluginId();
            //if(isset($info['complexity']) && isset($_SESSION['profile_plus']) && !$_SESSION['profile_plus']['Komplex'][$info['complexity']])continue;
    ?>

        <tr id="<?= htmlReady($anchor);?>" class="<?= $visibility; ?> <?= $pre_check != null ? ' quiet' : '' ?>">
            <td colspan = 3>

                <div class="plus_basic">

                    <!-- checkbox -->
                    <input type="checkbox" id="<?= $pluginname ?>" name="modules[]" value="<?= $plugin->getPluginId() ?>" <?= $val['activated'] ? 'checked' : '' ?>
                    onClick="form.submit()">

                    <div class="element_header">

                        <!-- Name -->
                        <label for="<?= $pluginname ?>"><strong><?= htmlReady($pluginname) ?></strong></label>

                        <?/* switch ($info['complexity']){
                                case 3: $complexname = 'Intensiv';
                                        break;
                                case 2: $complexname = 'Erweitert';
                                        break;
                                case 1: $complexname = 'Standard';
                                        break;
                                default: $complexname = 'Nicht angegeben';
                                        break;
                            }
                        ?>


                        <? if(isset($info['complexity'])) :
                            $color1 = isset($info['complexity']) ? "hsl(57, 100%, 50%)" : "hsl(0, 0%, 100%)";
                            $color2 = isset($info['complexity']) && $info['complexity']>1 ? "hsl(42, 100%, 50%)" : "hsl(0, 0%, 100%)";
                            $color3 = isset($info['complexity']) && $info['complexity']>2 ? "hsl(15, 100%, 50%)" : "hsl(0, 0%, 100%)";
                            $border_color1 = isset($info['complexity']) ? "hsl(57, 100%, 45%)" : "hsl(0, 0%, 80%)";
                            $border_color2 = isset($info['complexity']) && $info['complexity']>1 ? "hsl(42, 100%, 45%)" : "hsl(0, 0%, 80%)";
                            $border_color3 = isset($info['complexity']) && $info['complexity']>2 ? "hsl( 15, 100%, 45%)" : "hsl(0, 0%, 80%)";

                        ?>
                        <div class="complexity" title="Komplexität: <?= $complexname ?>">
                            <div class="complexity_element" style="background-color: <?= $color1?>; border-color: <?= $border_color1?>;"></div>
                            <div class="complexity_element" style="background-color: <?= $color2?>; border-color: <?= $border_color2?>;"></div>
                            <div class="complexity_element" style="background-color: <?= $color3?>; border-color: <?= $border_color3?>;"></div>
                        </div>
                        <? endif */?>

                    </div>

                    <div class="element_description">

                        <!-- icon -->
                        <? if(isset($info['icon'])) : ?>
                            <img class="plugin_icon text-bottom" alt="" src="<?= $URL."/".$info['icon'] ?> ">
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
                                <? endif ?>
                            <? endif ?>
                        </strong>

                    </div>

                    <!-- inhaltlöschenbutton -->
                    <? if ($val['type'] == 'plugin' && method_exists($plugin, 'deleteContent')) echo LinkButton::create(_('Inhalte löschen'), URLHelper::getURL("?deleteContent=true&name=" . $key), array('style' => 'float:right; z-index: 1;')); ?>
                    <? if ($val['type'] == 'modul' && $studip_module instanceOf StudipModule && method_exists($studip_module, 'deleteContent')) echo LinkButton::create(_('Inhalte löschen'), URLHelper::getURL("?deleteContent=true&name=" . $key), array('style' => 'float:right; z-index: 1;')); ?>

                </div>

              <? if ($_SESSION['profile_plus']['View'] == 'openall' || !isset($_SESSION['profile_plus'])){?>

                <div class="plus_expert">

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
                        <? foreach(explode(';',$info['keywords']) as $keyword){
                            echo '<li>'.htmlReady($keyword).'</li>'; }?>
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

                        <? if (!isset($info['descriptionlong'])) : ?>
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
    <? }
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
