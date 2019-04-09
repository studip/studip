<br>

<? if (trim($constraint['content'])) : ?>
<div class="posting">
    <div class="postbody">
        <div class="content"><?= formatReady(ForumEntry::killEdit($constraint['content'])) ?></div>
    </div>
</div>
<? endif ?>

<? if (!empty($list)) foreach ($list as $category_id => $entries) : ?>
<table class="default forum" data-category-id="<?= $category_id ?>">

    <colgroup>
        <col>
        <col>
        <col>
        <col>
    </colgroup>

    <thead>
        <tr>
            <th colspan="2"><?= _('Thema') ?></th>
            <th data-type="answers"><?= _("Beiträge") ?></th>
            <th data-type="last_posting"><?= _("letzte Antwort") ?></th>
        </tr>
    </thead>

    <tbody>
    
    <? if (!empty($entries)) foreach ($entries as $entry) :
        $jump_to_topic_id = ($entry['last_unread'] ?: $entry['topic_id']); ?>
 
    <tr data-area-id="<?= $entry['topic_id'] ?>">

        <td class="icon">
            <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $jump_to_topic_id .'#'. $jump_to_topic_id) ?>">
            <? if ($entry['chdate'] >= $visitdate && $entry['user_id'] != $GLOBALS['user']->id): ?>
                <? $jump_to_topic_id = $entry['topic_id'] ?>
                <?= Icon::create('forum+new', 'attention', ['title' => _('Dieser Eintrag ist neu!')])->asImg(16) ?>
            <? else : ?>
                <? $num_postings = ForumVisit::getCount($entry['topic_id'], $visitdate) ?>
                <? $text = ForumHelpers::getVisitText($num_postings, $entry['topic_id'], $constraint['depth']) ?>
                <? if ($num_postings > 0) : ?>
                    <?= Icon::create('forum', 'attention', ['title' => $text])->asImg(16) ?>
                <? else : ?>
                    <?= Icon::create('forum', 'info', ['title' => $text])->asImg(16) ?>
                <? endif ?>
            <? endif ?>

            <br>
            <?= Icon::create('lock-locked', 'info', ['title' => _('Dieses Thema ist geschlossen, es können keine neuen Beiträge erstellt werden.')])->asImg(16, ["id" => 'img-locked-'.$entry['topic_id'], "style" => $entry['closed']?'':'display: none']) ?>
            
            <?= Icon::create('staple', 'info', ['title' => _('Dieses Thema wurde hervorgehoben.')])->asImg(16, ["id" => 'img-sticky-'.$entry['topic_id'], "style" => $entry['sticky']?'':'display: none']) ?>
            </a>
        </td>

        <td class="areaentry">
            <div style="position: relative;">
                <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $entry['topic_id'] .'#'. $entry['topic_id']) ?>">
                    <span class="areaname"><?= htmlReady($entry['name_raw'] ?: _('Ohne Titel')) ?></span>
                </a>

                <span class="action-icons">
                    <? if (ForumPerm::has('move_thread', $seminar_id)) : ?>
                    <a href="javascript:STUDIP.Forum.moveThreadDialog('<?= $entry['topic_id'] ?>');" class="js">
                        <?= Icon::create('folder-full+move_right', 'clickable', ['title' => _('Dieses Thema verschieben')])->asImg(16, ["class" => 'move-thread']) ?>
                    </a>
                    
                    <div id="dialog_<?= $entry['topic_id'] ?>" style="display: none" title="<?= _('Bereich, in den dieser Thread verschoben werden soll:') ?>">
                        <? $path = ForumEntry::getPathToPosting($entry['topic_id']);
                        $parent = array_pop(array_slice($path, sizeof($path) - 2, 1)); ?>

                        <? foreach ($areas['list'] as $area_id => $area): ?>
                        <? if ($area_id != $parent['id']) : ?>
                        <div style="font-size: 16px; margin-bottom: 5px;">
                            <a href="<?= PluginEngine::getLink('coreforum/index/move_thread/'. $entry['topic_id'].'/'. $area_id) ?>">
                            <?= Icon::create('arr_2right', 'sort')->asImg() ?>
                            <?= htmlReady($area['name_raw']) ?>
                            </a>
                        </div>
                        <? endif ?>
                        <? endforeach ?>
                    </div>                    
                    <? endif ?>
                    
                    <? if (ForumPerm::has('remove_entry', $seminar_id)) : ?>
                    <a href="<?= PluginEngine::getURL('coreforum/index/delete_entry/' . $entry['topic_id']) ?>"
                        onClick="STUDIP.Forum.showDialog('<?= _('Möchten Sie dieses Thema wirklich löschen?') ?>',
                       '<?= PluginEngine::getURL('coreforum/index/delete_entry/' . $entry['topic_id'] .'?approve_delete=1&page='. ForumHelpers::getPage()) ?>',
                       'tr[data-area-id=<?= $entry['topic_id'] ?>] td.areaentry'); return false;">
                        <?= Icon::create('trash', 'clickable', ['title' => _('Dieses Thema löschen')])->asImg(16, ["class" => 'move-thread']) ?>
                    </a>
                    <? endif ?>
                    
                    <? if (ForumPerm::has('close_thread', $seminar_id) && $constraint['depth'] >= 1) : ?>
                        <? if ($entry['closed'] == 0) : ?>
                            <a href="<?= PluginEngine::getURL('coreforum/index/close_thread/' . $entry['topic_id'] . '/' 
                                . $constraint['topic_id'] .'/'. ForumHelpers::getPage()) ?>" 
                                onclick="STUDIP.Forum.closeThreadFromOverview('<?= $entry['topic_id'] ?>', '<?= $constraint['topic_id'] ?>', <?= ForumHelpers::getPage() ?>); return false;"
                                id="closeButton-<?= $entry['topic_id']; ?>">
                                <?= Icon::create('lock-locked', 'clickable', ['title' => _('Thema schließen')])->asImg(16) ?>
                            </a>
                        <? else : ?>
                            <a href="<?= PluginEngine::getURL('coreforum/index/open_thread/' . $entry['topic_id'] . '/' 
                                . $constraint['topic_id'] . '/' . ForumHelpers::getPage()) ?>"
                                onclick="STUDIP.Forum.openThreadFromOverview('<?= $entry['topic_id'] ?>', '<?= $constraint['topic_id'] ?>', <?= ForumHelpers::getPage() ?>); return false;"
                                id="closeButton-<?= $entry['topic_id']; ?>">
                                <?= Icon::create('lock-unlocked', 'clickable', ['title' => _('Thema öffnen')])->asImg(16) ?>
                            </a>
                        <? endif ?>
                    <? endif ?>
                    
                    <? if (ForumPerm::has('make_sticky', $seminar_id) && $constraint['depth'] >= 1) : ?>
                        <? if ($entry['sticky'] == 0) : ?>
                            <a href="<?= PluginEngine::getURL('coreforum/index/make_sticky/' . $entry['topic_id'] . '/' 
                                . $constraint['topic_id'] . '/0'); ?>" 
                                id="stickyButton-<?= $entry['topic_id']; ?>">
                                <?= Icon::create('staple', 'clickable', ['title' => _('Thema hervorheben')])->asImg(16) ?>
                            </a>
                        <? else : ?>
                            <a href="<?= PluginEngine::getURL('coreforum/index/make_unsticky/' . $entry['topic_id'] . '/' 
                                . $constraint['topic_id'] . '/0'); ?>" 
                                id="stickyButton-<?= $entry['topic_id']; ?>">
                                <?= Icon::create('staple', 'clickable', ['title' => _('Hervorhebung aufheben')])->asImg(16) ?>
                            </a>
                        <? endif ?>
                    <? endif ?>
                </span>

                <?= _("von") ?>
            <? if ($entry['anonymous']): ?>
                <?= _('Anonym') ?>
            <? endif; ?>
            <? if (!$entry['anonymous'] || $entry['user_id'] == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')): ?>
                <a href="<?= UrlHelper::getLink('dispatch.php/profile', ['username' => get_username($entry['user_id'])]) ?>">
                    <?= htmlReady(($temp_user = User::find($entry['user_id'])) ? $temp_user->getFullname() : $entry['author']) ?>
                </a>
                <? endif; ?>
                <?= _("am") ?> <?= strftime($time_format_string_short, (int)$entry['mkdate']) ?>
                <br>

                <? if ($entry['content_short'] && mb_strlen($entry['content'] > mb_strlen($entry['content_short']))) : ?>
                    <?= $entry['content_short'] ?>...
                <? else : ?>
                    <?= $entry['content_short'] ?>
                <? endif ?>
            </div>
        </td>

        <td class="postings">
            <?= $entry['num_postings'] ?>
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
            </a>
            <? endif; ?>
            <br>
            <?= _("am") ?> <?= strftime($time_format_string_short, (int)$entry['last_posting']['date']) ?>
            <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $entry['last_posting']['topic_id']) ?>#<?= $entry['last_posting']['topic_id'] ?>" alt="<?= $infotext ?>" title="<?= $infotext ?>">
                <?= Icon::create('link-intern', 'clickable', ['title' => $infotext = _("Direkt zum Beitrag...")])->asImg() ?>
            </a>
            <? else: ?>
            <br>
            <?= _('keine Antworten') ?>
            <? endif; ?>
        </td>
    </tr>
    <? endforeach; ?>
    </tbody>
</table>
<? endforeach ?>
