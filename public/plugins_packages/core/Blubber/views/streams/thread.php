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
<input type="hidden" id="base_url" value="plugins.php/blubber/streams/">
<input type="hidden" id="context_id" value="<?= htmlReady($thread->getId()) ?>">
<input type="hidden" id="stream" value="thread">
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<input type="hidden" id="stream_time" value="<?= time() ?>">
<input type="hidden" id="browser_start_time" value="">
<input type="hidden" id="orderby" value="mkdate">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>
<? if (Navigation::hasItem("/community/blubber")) : ?>
<p>
    <? switch ($thread['context_type']) {
        case "course":
            $overview_url = $controller->link_for('streams/forum', ['cid' => $thread['Seminar_id']]);
            break;
        case 'public':
            $overview_url = $controller->link_for(
                'streams/profile',
                $thread['external_contact']
                    ? ['user_id' => $thread['user_id'], 'extern' => 1]
                    : ['username' =>  get_username($thread['user_id'])]
            );
            break;
        default:
            $overview_url = $controller->link_for('streams/global');
    } ?>
    <a href="<?= URLHelper::getLink($overview_url) ?>">
        <?= Icon::create('arr_1left', 'clickable')->asImg(['class' => 'text-top']) ?>
        <?= _('Zurück zur Übersicht') ?>
    </a>
</p>
<? endif ?>

<ul id="blubber_threads" class="coursestream singlethread" aria-live="polite" aria-relevant="additions">
    <?= $this->render_partial("streams/_blubber.php", compact("thread")) ?>
</ul>
