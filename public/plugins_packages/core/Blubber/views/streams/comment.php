<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
if (!$last_visit) {
    $last_visit = object_get_visit(Context::getId(), "forum");
}
$author = $posting->getUser();
$author_name = $author->getName();
$author_url = $author->getURL();
?>
<li class="comment posting<?= $posting['mkdate'] > $last_visit ? " new" : "" ?>" id="posting_<?= $posting->getId() ?>" mkdate="<?= htmlReady($posting['mkdate']) ?>" data-autor="<?= htmlReady($posting['user_id']) ?>">
    <div class="avatar">
        <? if ($author_url) : ?>
        <a href="<?= URLHelper::getLink($author_url, [], true) ?>">
        <? endif ?>
            <div style="background-image: url('<?= $author->getAvatar()->getURL(Avatar::MEDIUM)?>');" class="avatar_image"<?= $author->isNew() ? ' title="'._("Nicht registrierter Nutzer").'"' : "" ?>></div>
        <? if ($author_url) : ?>
        </a>
        <? endif ?>
    </div>
    <div class="content_column">
        <div class="timer">
            <span class="time" data-timestamp="<?= (int) $posting['mkdate'] ?>" title="<?= date("j.n.Y H:i", $posting['mkdate']) ?>">
                <?= (date("j.n.Y", $posting['mkdate']) == date("j.n.Y")) ? sprintf(_("%s Uhr"), date("H:i", $posting['mkdate'])) : date("j.n.Y", $posting['mkdate']) ?>
            </span>
            <? if ($GLOBALS['perm']->have_studip_perm("tutor", $posting['Seminar_id']) or ($posting['user_id'] === $GLOBALS['user']->id) or $GLOBALS['perm']->have_perm("root")) : ?>
            <a href="#" class="edit" style="vertical-align: middle; opacity: 0.6;">
                <?= Icon::create('edit')->asImg(14, [
                    'title' => _('Bearbeiten'),
                ]) ?>
            </a>
            <a href="#" class="delete" style="vertical-align: middle; opacity: 0.6;">
                <?= Icon::create('trash')->asImg(14, [
                    'title' => _('Löschen'),
                    'data-confirm' => _('Möchten Sie diesen Beitrag wirklich löschen?'),
                ]) ?>
            </a>
            <? endif ?>
        </div>
        <div class="name">
            <? if ($author_url) : ?>
            <a href="<?= URLHelper::getLink($author_url, [], true) ?>">
            <? endif ?>
                <?= htmlReady($author_name) ?>
            <? if ($author_url) : ?>
            </a>
            <? endif ?>
        </div>
        <div class="content">
            <?= $posting->getContent() ?>
        </div>
        <?= $posting->getOpenGraphURLs()->render() ?>
    </div>
</li>
