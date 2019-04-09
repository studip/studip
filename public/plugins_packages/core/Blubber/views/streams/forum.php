<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
?>
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="base_url" value="plugins.php/blubber/streams/">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<input type="hidden" id="stream" value="course">
<input type="hidden" id="context_id" value="<?= Context::getId() ?>">
<input type="hidden" id="stream_time" value="<?= time() ?>">
<input type="hidden" id="search" value="<?= htmlReady($search) ?>">
<input type="hidden" id="browser_start_time" value="">
<input type="hidden" id="loaded" value="1">
<input type="hidden" id="orderby" value="discussion_time">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>

<div id="threadwriter" class="coursestream">
    <div class="row writer">
        <div id="context_selector" style="display: none;">
            <input type="hidden" id="context_type" value="course" checked="checked">
            <input type="hidden" id="context" value="<?= Context::getId() ?>">
        </div>
        <textarea id="new_posting" placeholder="<?= _("Schreib was, frag was.") ?>"><?= $search ? htmlReady($search) : "" ?></textarea>
        <label title="<?= _("Datei hochladen") ?>" class="uploader">
            <input type="file" style="display: none;" multiple>
            <?= Assets::img('ajax-indicator-black.svg', ['class' => "text-bottom uploading", 'width' => "16px", 'height' => "16px"]) ?>
            <?= Icon::create('upload', 'clickable')->asImg(['class' => "text-bottom upload"]) ?>
        </label>
    </div>
</div>

<? if ($GLOBALS['user']->id === "nobody") : ?>
<div id="identity_window_title" style="display: none;"><?= _("Namen eingeben") ?></div>
<div id="identity_window" style="display: none;">
    <input type="hidden" id="identity_window_textarea_id" value="">
    <table>
        <tbody>
            <tr>
                <td><?= _("Name") ?></td>
                <td><input type="text" id="anonymous_name" value="<?= htmlReady($_SESSION['anonymous_name']) ?>"></td>
            </tr>
            <tr>
                <td><?= _("Email") ?></td>
                <td><input type="text" id="anonymous_email" value="<?= htmlReady($_SESSION['anonymous_email']) ?>"></td>
            </tr>
            <tr>
                <? $_SESSION['blubber_anonymous_security'] or $_SESSION['blubber_anonymous_security'] = mb_substr(md5(uniqid()), 0, 5) ?>
                <td><?= _("Sicherheitsfrage! Schreibe folgendes rückwärts: ").strrev($_SESSION['blubber_anonymous_security']) ?></td>
                <td><input type="text" id="anonymous_security" value="<?= $_SESSION['anonymous_email'] ? htmlReady($_SESSION['blubber_anonymous_security']) : "" ?>"></td>
            </tr>
            <tr>
                <td></td>
                <td><?= \Studip\Button::create(_("abschicken"), ['onclick' => "STUDIP.Blubber.submitAnonymousPosting();"]) ?></td>
            </tr>
        </tbody>
    </table>
    <br>
</div>
<? endif ?>
<ul id="blubber_threads" class="coursestream" aria-live="polite" aria-relevant="additions">
    <? foreach ($threads as $thread) : ?>
    <?= $this->render_partial("streams/_blubber.php", ['thread' => $thread]) ?>
    <? endforeach ?>
    <? if ($more_threads) : ?>
    <li class="more"><?= Assets::img("ajax_indicator_small.gif", ['alt' => "loading"]) ?></li>
    <? endif ?>
</ul>

<?php

$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/blubber-sidebar.png');

$controller->addTagCloudWidgetToSidebar($tags, 'forum');


