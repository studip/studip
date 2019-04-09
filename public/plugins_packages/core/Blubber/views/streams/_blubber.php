<?
$last_visit = object_get_visit(Context::getId(), "forum");
BlubberPosting::$course_hashes = ($thread['context_type'] === "course" ? $thread['Seminar_id'] : false);
$related_users = $thread['context_type'] === "private" ? $thread->getRelatedUsers() : [];
$author = $thread->getUser();
$author_name = $author->getName();
$author_url = $author->getURL();
$commentable = $GLOBALS['perm']->have_perm("autor") ? true : (bool) $commentable;
?>
<li id="posting_<?= htmlReady($thread->getId()) ?>" data-mkdate="<?= htmlReady($thread['discussion_time']) ?>" data-discussion_time="<?= htmlReady($thread['discussion_time']) ?>" class="thread posting<?= $last_visit < $thread['mkdate'] ? " new" : "" ?> <?= $thread['context_type'] ?>" data-autor="<?= htmlReady($thread['user_id']) ?>">
    <? $thread['discussion_time'] ?>
    <div class="hiddeninfo">
        <input type="hidden" name="context" value="<?= htmlReady($thread['Seminar_id']) ?>">
        <input type="hidden" name="context_type" value="<?= $thread['Seminar_id'] === $thread['user_id'] ? "public" : "course" ?>">
    </div>
    <? if ($thread['context_type'] === "course") : ?>
        <a href="<?= URLHelper::getLink("plugins.php/blubber/streams/forum", ['cid' => $thread['Seminar_id']]) ?>"
            <? $title = get_object_name($thread['Seminar_id'], "sem") ?>
           title="<?= _("Veranstaltung")." ".htmlReady($title['name']) ?>"
           class="contextinfo"
           >
            <div class="name"><?= htmlReady(Course::find($thread['Seminar_id'])->name) ?></div>
        </a>
    <? elseif($thread['context_type'] === "private") : ?>
        <?
        if (count($related_users) > 20) {
            $title = _("Privat: ").sprintf(_("%s Personen"), count($related_users));
        } else {
            $title = _("Privat: ");
            foreach ($related_users as $key => $user_id) {
                if ($key > 0) {
                    $title .= ", ";
                }
                $title .= htmlReady(get_fullname($user_id, 'no_title'));
            }
        }
        ?>
        <div class="contextinfo" title="<?= htmlReady($title) ?>">
            <div class="name">
                <?= _("Privat") ?>:
                <? foreach (array_slice($related_users, 0, 3) as $key => $user_id) {
                    if ($key > 0) {
                        echo ", ";
                    }
                    echo htmlReady(get_fullname($user_id, 'no_title'));
                } ?>
                <? if (count($related_users) > 3) : ?>
                    , ...
                <? endif ?>
            </div>
        </div>
        <div class="related_users">
            <? foreach ($related_users as $user_id) {
                echo Avatar::getAvatar($user_id)->getImageTag(Avatar::SMALL);
            } ?>
        </div>
    <? else : ?>
        <div class="contextinfo" title="<?= _("Öffentlich") ?>">
            <div class="name"><?= _("Öffentlich") ?></div>
        </div>
    <? endif ?>
    <? if ($thread['context_type'] === "public") : ?>
        <? $sharingusers = $thread->getSharingUsers() ?>
        <? $sharing_user_ids = array_map(function ($v) { return $v['user_id']; }, $sharingusers) ?>
        <div class="reshares<?= count($sharingusers) > 0 ? " reshared" : "" ?>">
            <? if (count($sharingusers)) : ?>
                <? if ((!User::findCurrent()->isFriendOf($thread) || $thread['external_contact']) && ($GLOBALS['user']->id !== $thread['user_id'])) : ?>
                    <? $sharingcontacts = "" ?>
                    <? $othersharing = 0 ?>
                    <? foreach ($sharingusers as $key => $user) {
                        if (User::findCurrent()->isFriendOf($user)) {
                            $url = $user->getURL();
                            $name = $user->getName();
                            if ($url) {
                                $sharingcontacts .= '<a href="'.$url.'" title="'.htmlReady($name).'">';
                            }
                            $sharingcontacts .= $user->getAvatar()->getImageTag(Avatar::SMALL, ['title' => $name]);
                            if ($url) {
                                $sharingcontacts .= '</a>';
                            }
                        } else {
                            $othersharing++;
                        }
                    } ?>
                    <? if ($sharingcontacts) : ?>
                        <?= $sharingcontacts ?>
                        <a href="#" class="open_reshare_context"><?= $othersharing > 0 ? sprintf(_("und %s weitere haben das weitergesagt"), $othersharing) : _("haben das weitergesagt") ?></a>
                    <? else : ?>
                        <a href="#" class="open_reshare_context"><?= $othersharing > 1 ? sprintf(_("%s Personen haben das weitergesagt"), $othersharing) : _("Eine Person hat das weitergesagt") ?></a>
                    <? endif ?>
                <? else : ?>
                    <a href="#" class="open_reshare_context"><?= count($sharingusers) > 1 ? sprintf(_("%s Personen haben das weitergesagt"), count($sharingusers)) : _("Eine Person hat das weitergesagt") ?></a>
                <? endif ?>
            <? endif ?>
            <span class="reshare_link">
        <? if (!in_array($GLOBALS['user']->id, $sharing_user_ids) && $GLOBALS['user']->id !== $thread['user_id']) : ?>
            <?= Icon::create('blubber', 'clickable')->asImg(['class' => "text-bottom reshare_blubber", 'title' => _("Diesen Blubber weitersagen")]) ?>
        <? elseif($GLOBALS['user']->id !== $thread['user_id']) : ?>
            <a href="#" class="open_reshare_context"><?= Icon::create('blubber', 'inactive')->asImg(['class' => "text-bottom", 'title' => _("Weitergesagt von diesen Personen")]) ?></a>
        <? endif ?>
        </span>
        </div>
    <? endif ?>
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
            <a href="<?= URLHelper::getLink('plugins.php/blubber/streams/thread/' . $thread->getId(), ['cid' => $thread['Seminar_id']]) ?>"
                   class="permalink"
                   title="<?= date("j.n.Y H:i", $thread['mkdate']) ?>">
                <span class="time" data-timestamp="<?= (int) $thread['mkdate'] ?>">
                    <?= (date("j.n.Y", $thread['mkdate']) == date("j.n.Y")) ? sprintf(_("%s Uhr"), date("H:i", $thread['mkdate'])) : date("j.n.Y", $thread['mkdate']) ?>
                </span>
            </a>
            <? if (($thread['Seminar_id'] !== $thread['user_id'] && $GLOBALS['perm']->have_studip_perm("tutor", $thread['Seminar_id']))
                or ($thread['user_id'] === $GLOBALS['user']->id)
                or $GLOBALS['perm']->have_perm("root")) : ?>
                <a href="#" class="edit icon">
                    <?= Icon::create('edit')->asImg(14, [
                        'title' => _('Bearbeiten'),
                    ]) ?>
                </a>
                <a href="#" class="delete icon">
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
            <?= $thread->getContent() ?>
        </div>
        <div class="additional_tags"><? foreach ($thread->getTags() as $tag) : ?>
                <? if (mb_stripos($content, "#".$tag) === false) : ?>
                    <? $link = $thread['context_type'] === "course" ? URLHelper::getLink("plugins.php/blubber/streams/forum", ['cid' => $thread['Seminar_id'], 'hash' => $tag]) : URLHelper::getLink("plugins.php/blubber/streams/global", ['hash' => $tag]) ?>
                    <a href="<?= $link ?>"><?= htmlReady("#".$tag) ?></a>
                <? endif ?>
            <? endforeach ?></div>
        <?= $thread->getOpenGraphURLs()->render() ?>
    </div>
    <ul class="comments"><?
        $postings = $thread->getChildren(0, 4);
        if ($postings) : ?>
            <? $more_comments = $thread->getNumberOfChildren() - 3 ?>
            <? if ($more_comments > 0) : ?>
                <li class="more">
                    <?= sprintf(ngettext('%u weiterer Kommentar anzeigen', '%u weitere Kommentare anzeigen', $more_comments), $more_comments) ?>
                </li>
            <? endif; ?>
            <? foreach (array_slice(array_reverse($postings), -3) as $posting) : ?>
                <?= $this->render_partial("streams/comment.php", ['posting' => $posting, 'last_visit' => $last_visit]) ?>
            <? endforeach ?>
        <? endif;
    ?></ul>
    <? if ($commentable) : ?>
        <div class="writer">
            <textarea placeholder="<?= _("Kommentiere dies") ?>" aria-label="<?= _("Kommentiere dies") ?>" id="writer_<?= md5(uniqid()) ?>"></textarea>
            <label title="<?= _("Datei hochladen") ?>" class="uploader">
                <input type="file"
                       style="display: none;"
                       multiple>
                <?= Assets::img('ajax-indicator-black.svg', ['class' => "text-bottom uploading", 'width' => "16px", 'height' => "16px"]) ?>
                <?= Icon::create('upload', 'clickable')->asImg(['class' => "text-bottom upload"]) ?>
            </label>
        </div>
    <? endif ?>
</li>
