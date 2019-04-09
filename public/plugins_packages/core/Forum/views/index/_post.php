<? if (!is_array($highlight)) $highlight = []; ?>
<? $is_new =  ((isset($visitdate) && $post['mkdate'] >= $visitdate) || !(isset($visitdate))) ?>
<? if (!$constraint) $constraint = ForumEntry::getConstraints (ForumEntry::getParentTopicId($post['topic_id'])) ?>

<? $can_edit_closed = !ForumEntry::isClosed($constraint['topic_id'])
        || (ForumEntry::isClosed($constraint['topic_id']) && ForumPerm::has('edit_closed', $constraint['seminar_id'])) ?>

<? $perms = [
    'edit'         => ForumPerm::hasEditPerms($post['topic_id']),
    'edit_closed'  => ForumPerm::has('edit_closed', $constraint['seminar_id']),
    'remove_entry' => ForumPerm::has('remove_entry', $constraint['seminar_id']),
] ?>

<!-- Anker, um zu diesem Posting springen zu können -->
<a name="<?= $post['topic_id'] ?>"></a>

<form method="post" data-topicid="<?= $post['topic_id'] ?>" action="<?= PluginEngine::getLink('coreforum/index/update_entry/' . $post['topic_id']) ?>">
    <?= CSRFProtection::tokenTag() ?>

<div class="real_posting posting<?= $highlight_topic == $post['topic_id'] ? ' highlight' : '' ?>" style="position: relative;" id="forumposting_<?= htmlReady($post['topic_id']) ?>">
    <a class="marked" href="<?= PluginEngine::getLink('coreforum/index/unset_favorite/'. $post['topic_id']) ?>"
            onClick="STUDIP.Forum.unsetFavorite('<?= $post['topic_id'] ?>'); return false;" title="<?= _('Beitrag nicht mehr merken') ?>"
            <?= ($post['fav']) ?: 'style="display: none;"' ?> data-topic-id="<?= $post['topic_id'] ?>">
        <div></div>
    </a>

    <div class="postbody">
        <div class="title">

            <div class="small_screen" style="margin-bottom: 5px">
                <? if ($post['anonymous']): ?>
                    <strong><?= _('Anonym') ?></strong>
                    <?= strftime($time_format_string_short, (int)$post['mkdate']) ?>
                <? elseif (!$post['user_id']) : ?>
                    <?= Avatar::getAvatar('nobody')->getImageTag(Avatar::SMALL,
                        ['title' => _('Stud.IP')]) ?>
                    <?= _('von Stud.IP erstellt') ?>,
                    <?= strftime($time_format_string_short, (int)$post['mkdate']) ?>
                <? else : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' =>  get_username($post['user_id'])]) ?>">
                    <?= Avatar::getAvatar($post['user_id'])->getImageTag(Avatar::SMALL,
                        ['title' => get_username($post['user_id'])]) ?>

                    <? if ($post['user_id'] == 'nobody' && $post['author']) : ?>
                        <?= htmlReady($post['author']) ?>,
                    <? else : ?>
                        <?= htmlReady(get_fullname($post['user_id'])) ?>,
                    <? endif ?>
                    <?= strftime($time_format_string_short, (int)$post['mkdate']) ?>
                </a>
                <? endif ?>

                <br>
            </div>

            <? if ($post['depth'] < 3) : ?>
            <span data-edit-topic="<?= $post['topic_id'] ?>" <?= $edit_posting == $post['topic_id'] ? '' : 'style="display: none;"' ?>>
                <input type="text" name="name" value="<?= htmlReady($post['name_raw']) ?>" data-reset="<?= htmlReady($post['name_raw']) ?>" style="width: 100%">
            </span>
            <? else : ?>
                <? $parent_topic = ForumEntry::getConstraints(ForumEntry::getParentTopicId($post['topic_id'])) ?>

                <? if($constraint['closed']) : ?>
                <?= Icon::create('lock-locked', 'info', ['title' => _('Dieses Thema wurde geschlossen. Sie können daher nicht auf diesen Beitrag antworten.')])->asImg(16) ?>
                <? endif ?>

                <span data-edit-topic="<?= $post['topic_id'] ?>">
                    <span name="name" value="<?= htmlReady($parent_topic['name']) ?>"></span>
                </span>
            <? endif ?>

            <span data-show-topic="<?= $post['topic_id'] ?>">
                <a href="<?= PluginEngine::getLink('coreforum/index/index/' . $post['topic_id'] .'?'. http_build_query(['highlight' => $highlight]) ) ?>#<?= $post['topic_id'] ?>">
                <? if ($show_full_path) : ?>
                    <?= ForumHelpers::highlight(htmlReady(implode(' >> ', ForumEntry::getFlatPathToPosting($post['topic_id']))), $highlight) ?>
                <? elseif ($post['depth'] < 3) : ?>
                <span data-topic-name="<?= $post['topic_id'] ?>">
                    <? if ($edit_posting != $post['topic_id']) : ?>
                    <?= ($post['name_raw'] && $post['depth'] < 3) ? ForumHelpers::highlight(htmlReady($post['name_raw']), $highlight) : ''?>
                    <? endif ?>
                </span>
                <? endif ?>
                </a>
            </span>
        </div>

        <!-- Postinginhalt -->
        <div class="content">
            <span data-edit-topic="<?= $post['topic_id'] ?>" <?= $edit_posting == $post['topic_id'] ? '' : 'style="display: none;"' ?>>
                <textarea data-textarea="<?= $post['topic_id'] ?>" data-reset="<?= wysiwygReady($post['content_raw']) ?>" name="content" class="add_toolbar wysiwyg"><?= wysiwygReady($post['content_raw']) ?></textarea>
            </span>

            <span data-show-topic="<?= $post['topic_id'] ?>" data-topic-content="<?= $post['topic_id'] ?>" <?= $edit_posting != $post['topic_id'] ? '' : 'style="display: none;"' ?>>
                <?= ForumHelpers::highlight($post['content'], $highlight) ?>
                <?= OpenGraph::extract(formatReady(ForumEntry::removeQuotes($post['content_raw'])))->render() ?>
            </span>
        </div>

        <!-- Buttons for this Posting -->
        <div class="buttons">
            <div class="button-group">

        <span data-edit-topic="<?= $post['topic_id'] ?>" <?= ($edit_posting == $post['topic_id']) ? '' : 'style="display: none;"' ?>>
            <!-- Buttons für den Bearbeitungsmodus -->
            <?= Studip\Button::createAccept(_('Änderungen speichern'), '',
                ['onClick' => "STUDIP.Forum.saveEntry('". $post['topic_id'] ."'); return false;"]) ?>

            <?= Studip\LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('coreforum/index/index/'. $post['topic_id'] .'#'. $post['topic_id']),
                ['onClick' => "STUDIP.Forum.cancelEditEntry('". $post['topic_id'] ."'); return false;"]) ?>

            <?= Studip\LinkButton::create(_('Vorschau'), "javascript:STUDIP.Forum.preview('". $post['topic_id'] ."', 'preview_". $post['topic_id'] ."');") ?>
        </span>

        <span data-show-topic="<?= $post['topic_id'] ?>" <?= $edit_posting != $post['topic_id'] ? '' : 'style="display: none;"' ?>>
            <!-- Aktions-Buttons für diesen Beitrag -->


            <? if (ForumPerm::has('add_entry', $constraint['seminar_id'])) : ?>
                <?= Studip\LinkButton::create(_('Beitrag zitieren'), PluginEngine::getURL('coreforum/index/index/' . $post['topic_id'] .'?cite=1'), [
                    'onClick' => "javascript:STUDIP.Forum.citeEntry('". $post['topic_id'] ."'); return false;",
                    'class'   => !$perms['edit_closed'] ? 'hideWhenClosed' : '',
                    'style'   => !$can_edit_closed ? 'display: none' : ''
                ]) ?>
            <? endif ?>

            <? if ($perms['edit']) : ?>
                <?= Studip\LinkButton::create(_('Beitrag bearbeiten'), PluginEngine::getUrl('coreforum/index/index/'
                      . $post['topic_id'] .'/?edit_posting=' . $post['topic_id']), [
                          'onClick' => "STUDIP.Forum.editEntry('". $post['topic_id'] ."'); return false;",
                          'class'   => !$perms['edit_closed'] ? 'hideWhenClosed' : '',
                          'style'   => !$can_edit_closed ? 'display: none' : ''
                ]) ?>
            <? endif ?>

            <span <?= (!$perms['edit_close'] && !$perms['remove_entry']) ? 'class="hideWhenClosed"': '' ?>
                <?= (!$perms['edit'] && !$perms['remove_entry']) ? 'style="display: none"' : '' ?>>
                <? $confirmLink = PluginEngine::getURL('coreforum/index/delete_entry/' . $post['topic_id'])  ?>
                <? $confirmLinkApproved = PluginEngine::getURL('coreforum/index/delete_entry/' .
                    $post['topic_id'] . '?approve_delete=1&section=' . $section .'&page=' . ForumHelpers::getPage())  ?>
                <? if ($constraint['depth'] == $post['depth']) : /* this is not only a posting, but a thread */ ?>
                    <? $confirmText = _('Wenn Sie diesen Beitrag löschen wird ebenfalls das gesamte Thema gelöscht. Sind Sie sicher, dass Sie das tun möchten?')  ?>
                    <?= Studip\LinkButton::create(_('Thema löschen'), $confirmLink,
                        ['onClick' => "STUDIP.Forum.showDialog('$confirmText', '$confirmLinkApproved'); return false;"]) ?>
                <? else : ?>
                    <? $confirmText = _('Möchten Sie diesen Beitrag wirklich löschen?') ?>
                    <?= Studip\LinkButton::create(_('Beitrag löschen'), $confirmLink,
                        ['onClick' => "STUDIP.Forum.showDialog('$confirmText', '$confirmLinkApproved'); return false;"]) ?>
                <? endif ?>
            </span>

            <? if (ForumPerm::has('forward_entry', $seminar_id)) : ?>
            <?= Studip\LinkButton::create(_('Beitrag weiterleiten'),
                    "javascript:STUDIP.Forum.forwardEntry('". $post['topic_id'] ."')", ['class' => 'js']) ?>
            <? endif ?>
        </span>
            </div>
        </div>

    </div>

    <? if ($perms['edit']) : ?>
    <span data-edit-topic="<?= $post['topic_id'] ?>" <?= $edit_posting == $post['topic_id'] ? '' : 'style="display: none;"' ?>>
        <dl class="postprofile">
            <dt>
                <? if (!Config::get()->WYSIWYG): ?>
                    <?= $this->render_partial('index/_smiley_favorites', ['textarea_id' => $post['topic_id']]) ?>
                <? endif; ?>
            </dt>
        </dl>
    </span>
    <? endif ?>

    <!-- Infobox rechts neben jedem Posting -->
    <span data-show-topic="<?= $post['topic_id'] ?>" <?= $edit_posting != $post['topic_id'] ? '' : 'style="display: none;"' ?>>
        <dl class="postprofile">
            <? if ($post['anonymous']): ?>
                <dd class="anonymous_post" data-profile="<?= $post['topic_id'] ?>"><strong><?= _('Anonym') ?></strong></dd>
            <? endif; ?>
            <? if (!$post['anonymous'] || $post['user_id'] == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')): ?>
            <dt>
                <? if ($post['user_id'] != 'nobody' && $post['user_id']) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => get_username($post['user_id'])]) ?>">
                    <?= Avatar::getAvatar($post['user_id'])->getImageTag(Avatar::MEDIUM,
                        ['title' => get_username($post['user_id'])]) ?>
                </a>
                <br>
                <? endif ?>

                <? if ($post['user_id'] == 'nobody') : ?>
                    <?= Icon::create('community', 'info')->asImg() ?>
                    <span class="username" data-profile="<?= $post['topic_id'] ?>">
                        <?= htmlReady($post['author']) ?>
                    </span>
                <? elseif ($post['user_id']) : ?>

                    <!-- Online-Status -->
                    <? $status = ForumHelpers::getOnlineStatus($post['user_id']) ?>
                    <? if ($status == 'available') : ?>
                        <img src="<?= $picturepath ?>/community.png" title="<?= _('Online') ?>">
                    <? elseif ($status == 'away') : ?>
                        <?= Icon::create('community', 'inactive', ['title' => _('Abwesend')])->asImg() ?>
                    <? elseif ($status == 'offline') : ?>
                        <?= Icon::create('community', 'info', ['title' => _('Offline')])->asImg() ?>
                    <? endif ?>

                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => get_username($post['user_id'])])?>">
                        <span class="username" data-profile="<?= $post['topic_id'] ?>">
                            <?= htmlReady(get_fullname($post['user_id'])) ?>
                        </span>
                    </a>
                <? endif ?>
            </dt>

            <dd>
                <?= ForumHelpers::translate_perm($GLOBALS['perm']->get_studip_perm($constraint['seminar_id'], $post['user_id']))?>
            </dd>
            <? if ($post['user_id']) : ?>
            <dd>
                Beiträge:
                <?= ForumEntry::countUserEntries($post['user_id']) ?><br>
                <?= _('Erhaltene "Gefällt mir!":') ?>
                <?= ForumLike::receivedForUser($post['user_id']) ?>
            </dd>
            <? endif ?>
            <? endif; ?>
            <dd>
                <? if (!$post['user_id']) : ?>
                    <?= _('von Stud.IP erstellt') ?><br>
                <? endif ?>
            </dd>

            <dd class="posting_icons">
                <!-- Favorit -->
                <span id="favorite_<?= $post['topic_id'] ?>">
                    <?= $this->render_partial('index/_favorite', ['topic_id' => $post['topic_id'], 'favorite' => $post['fav']]) ?>
                </span>

                <!-- Permalink -->
                <a href="<?= PluginEngine::getLink('coreforum/index/index/' . $post['topic_id'] .'#'. $post['topic_id']) ?>">
                    <?= Icon::create('group', 'clickable', ['title' => _('Link zu diesem Beitrag')])->asImg() ?>
                </a>
                <br>

                <!-- Like -->
                <span class="likes" id="like_<?= $post['topic_id'] ?>">
                    <?= $this->render_partial('index/_like', ['topic_id' => $post['topic_id']]) ?>
                </span>
            </dd>

            <? foreach (PluginEngine::sendMessage('PostingApplet', 'getHTML', $post['name_raw'], $post['content_raw'],
                    PluginEngine::getLink('coreforum/index/index/' . $post['topic_id'] .'#'. $post['topic_id']),
                    $post['user_id']) as $applet_data) : ?>
            <dd>
                <?= $applet_data ?>
            </dd>
            <? endforeach ?>
        </dl>

        <? if ($is_new): ?>
        <span class="new_posting">
            <?= Icon::create('forum+new', 'attention', ['title' => _("Dieser Beitrag ist seit Ihrem letzten Besuch hinzugekommen.")])->asImg(16) ?>
        </span>
        <? endif ?>
    </span>

    <div class="clear"></div>
</div>
</form>

<?= $this->render_partial('index/_preview', ['preview_id' => 'preview_' . $post['topic_id']]) ?>
