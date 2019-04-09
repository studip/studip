<? $jump_to_topic_id = $entry['topic_id'] ?>
<tr id="tutorArea" data-area-id="<?= $entry['topic_id'] ?>" <?= (ForumPerm::has('sort_area', $seminar_id)) ? 'class="movable"' : '' ?>>
    <td class="icon">
        <? if (ForumPerm::has('sort_area', $seminar_id)) : ?>
        <img src="<?= $picturepath ?>/anfasser_48.png" class="handle js" id="tutorMoveArea">
        <? endif ?>

        <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $jump_to_topic_id .'#'. $jump_to_topic_id) ?>">
        <? if ($entry['chdate'] >= $visitdate && $entry['user_id'] != $GLOBALS['user']->id): ?>
            <?= Icon::create('forum+new', 'attention', ['title' => _('Dieser Eintrag ist neu!')])->asImg(16, ["id" => 'tutorNotificationIcon', "style" => 'margin-bottom: 15px;']) ?>
        <? else : ?>
            <? $num_postings = ForumVisit::getCount($entry['topic_id'], $visitdate) ?>
            <? $text = ForumHelpers::getVisitText($num_postings, $entry['topic_id'], $constraint['depth']) ?>
            <? if ($num_postings > 0) : ?>
                <?= Icon::create('forum', 'attention', ['title' => $text])->asImg(16, ["id" => 'tutorNotificationIcon', "style" => 'margin-bottom: 15px;']) ?>
            <? else : ?>
                <?= Icon::create('forum', 'info', ['title' => $text])->asImg(16, ["id" => 'tutorNotificationIcon', "style" => 'margin-bottom: 15px;']) ?>
            <? endif ?>
        <? endif ?>
        </a>
    </td>
    <td class="areaentry">
        <div style="position: relative;<?= Request::get('edit_area') == $entry['topic_id'] ? 'height: auto;' : '' ?>">

            <span class="areadata" <?= Request::get('edit_area') != $entry['topic_id'] ? '' : 'style="display: none;"' ?>>
                <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $jump_to_topic_id .'#'. $jump_to_topic_id) ?>">
                    <span class="areaname"><?= htmlReady($entry['name_raw']) ?></span>
                </a>
                <div class="areacontent" data-content="<?= htmlReady($entry['content_raw']) ?>">
                    <? $description = ForumEntry::killFormat(ForumEntry::killEdit($entry['content_raw'])) ?>
                    <?= htmlReady(mb_substr($description, 0, 150))
                    ?><?= (mb_strlen($description) > 150) ? '&hellip;' : '' ?>
                </div>
            </span>


            <? if (ForumPerm::has('edit_area', $seminar_id) && Request::get('edit_area') == $entry['topic_id']) : ?>
            <span style="text-align: center;">
                <div style="width: 90%">
                    <?= $this->render_partial('area/_edit_area_form', compact('entry')) ?>
                </div>
            </span>
            <? endif ?>

            <span class="action-icons" <? if(ForumPerm::has('edit_area', $seminar_id)) : ?> id="tutorAreaIcons"<? endif ?> <?= Request::get('edit_area') != $entry['topic_id'] ? '' : 'style="display: none;"' ?>>
                <? if (ForumPerm::has('edit_area', $seminar_id)) : ?>
                    <? if ($issue_id = ForumIssue::getIssueIdForThread($entry['topic_id'])) : ?>
                    <a href="<?= URLHelper::getURL('dispatch.php/course/topics/edit/' . $issue_id) ?>">
                        <?= tooltipIcon(_('Dieser Bereich ist einem Thema zugeordnet und kann hier nicht editiert werden. '
                                . 'Die Angaben können im Ablaufplan angepasst werden.'), true) ?>
                    </a>
                    <? else: ?>
                    <a href="<?= PluginEngine::getLink('coreforum/index/?edit_area=' . $entry['topic_id']) ?>"
                        onClick="STUDIP.Forum.editArea('<?= $entry['topic_id'] ?>');return false;">
                        <?= Icon::create('edit', 'clickable', ['title' => 'Name/Beschreibung des Bereichs ändern'])->asImg(16, ["class" => 'edit-area']) ?>
                    </a>
                    <? endif ?>
                <? endif ?>

                <? if (ForumPerm::has('remove_area', $seminar_id)) : ?>
                <a href="<?= PluginEngine::getLink('coreforum/index/delete_entry/' . $entry['topic_id']) ?>"
                   onClick="STUDIP.Forum.deleteArea(this, '<?= $entry['topic_id'] ?>'); return false;">
                    <?= Icon::create('trash', 'clickable', ['title' => 'Bereich mitsamt allen Einträgen löschen!'])->asImg(16, ["class" => 'delete-area']) ?>
                </a>
                <? endif ?>
            </span>
        </div>
    </td>

    <td class="postings">
        <span id="tutorNumPostings">
            <?= ($entry['num_postings'] > 0) ? ($entry['num_postings'] - 1) : 0 ?>
        </span>
    </td>

    <td class="answer">
        <? if (is_array($entry['last_posting'])) : ?>
        <?= _("von") ?>
        <? if ($entry['last_posting']['anonymous']): ?>
            <?= _('Anonym') ?>
        <? endif; ?>
        <? if (!$entry['last_posting']['anonymous'] || $entry['last_posting']['user_id'] == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')): ?>
        <a href="<?= UrlHelper::getLink('dispatch.php/profile', ['username' => $entry['last_posting']['username']]) ?>">
            <?= htmlReady(($temp_user = User::find($entry['last_posting']['user_id'])) ? $temp_user->getFullname() : $entry['last_posting']['user_fullname']) ?>
        </a><br>
        <?= _("am") ?> <?= strftime($time_format_string_short, (int)$entry['last_posting']['date']) ?>
        <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $entry['last_posting']['topic_id']) ?>#<?= $entry['last_posting']['topic_id'] ?>" alt="<?= $infotext ?>" title="<?= $infotext ?>">
            <?= Icon::create('link-intern', 'clickable', ['title' => $infotext = _("Direkt zum Beitrag..."), 'id' => 'tutorLatestAnswer'])->asImg() ?>
        </a>
        <? else: ?>
        <br>
        <?= _('keine Antworten') ?>
        <? endif; ?>
        <? endif; ?>
    </td>

    <td class="icon" style="text-align: right; padding-right: 2px;">
        <? if (ForumPerm::has('sort_area', $seminar_id)) : ?>
        <img src="<?= $picturepath ?>/anfasser_48.png" class="handle js" id="tutorMoveArea">
        <? endif ?>
    </td>

</tr>
