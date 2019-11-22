<br>

<? if (trim($constraint['content'])) : ?>
<div class="posting">
    <div class="postbody">
        <div class="content"><?= formatReady(ForumEntry::killEdit($constraint['content'])) ?></div>
    </div>
</div>
<? endif ?>

<form action="#" method="post">
<? if (!empty($list)) foreach ($list as $category_id => $entries) : ?>
    <table class="default forum" data-category-id="<?= $category_id ?>">
        <colgroup>
            <col>
            <col>
            <col>
            <col class="hidden-tiny-down">
            <col style="width: 24px">
        </colgroup>

        <thead>
            <tr>
                <th colspan="2"><?= _('Thema') ?></th>
                <th data-type="answers"><?= _("Beiträge") ?></th>
                <th data-type="last_posting" class="hidden-tiny-down">
                    <?= _('letzte Antwort') ?>
                </th>
                <th></th>
            </tr>
        </thead>

        <tbody>

        <? if (!empty($entries)) foreach ($entries as $entry) :
            $jump_to_topic_id = ($entry['last_unread'] ?: $entry['topic_id']); ?>

        <tr data-area-id="<?= $entry['topic_id'] ?>">

            <td class="icon">
                <a href="<?= $controller->link_for("index/index/{$jump_to_topic_id}#{$jump_to_topic_id}") ?>">
                <? if ($entry['chdate'] >= $visitdate && $entry['user_id'] != $GLOBALS['user']->id): ?>
                    <?= Icon::create('forum+new', Icon::ROLE_ATTENTION)->asImg([
                        'title' => _('Dieser Eintrag ist neu!'),
                    ]) ?>
                <? else : ?>
                    <? $num_postings = ForumVisit::getCount($entry['topic_id'], $visitdate) ?>
                    <?= Icon::create('forum', $num_postings > 0 ? Icon::ROLE_ATTENTION : Icon::ROLE_INFO)->asImg([
                        'title' => htmlReady(ForumHelpers::getVisitText($num_postings, $entry['topic_id'], $constraint['depth'])),
                    ]) ?>
                <? endif ?>

                    <br>

                    <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg([
                        'title' => _('Dieses Thema ist geschlossen, es können keine neuen Beiträge erstellt werden.'),
                        'id'    => "img-locked-{$entry['topic_id']}",
                        'style' => $entry['closed'] ? '' : 'display: none',
                    ]) ?>

                    <?= Icon::create('staple', Icon::ROLE_INFO)->asImg([
                        'title' => _('Dieses Thema wurde hervorgehoben.'),
                        'id'    => "img-sticky-{$entry['topic_id']}",
                        'style' => $entry['sticky'] ? '' : 'display: none',
                    ]) ?>
                </a>
            </td>

            <td class="areaentry">
                <div style="position: relative;">
                    <a href="<?= PluginEngine::getLink('coreforum/index/index/'. $entry['topic_id'] .'#'. $entry['topic_id']) ?>">
                        <span class="areaname"><?= htmlReady($entry['name_raw'] ?: _('Ohne Titel')) ?></span>
                    </a>

                    <?= _("von") ?>
                <? if ($entry['anonymous']): ?>
                    <?= _('Anonym') ?>
                <? endif; ?>
                <? if (!$entry['anonymous'] || $entry['user_id'] == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')): ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => get_username($entry['user_id'])]) ?>">
                        <?= htmlReady(($temp_user = User::find($entry['user_id'])) ? $temp_user->getFullname() : $entry['author']) ?>
                    </a>
                    <? endif; ?>
                    <?= _("am") ?> <?= strftime($time_format_string_short, (int)$entry['mkdate']) ?>
                    <br>

                    <?= htmlReady($entry['content_short']) ?>
                </div>
            </td>

            <td class="postings">
                <?= number_format($entry['num_postings'], 0, ',', '.') ?>
            </td>

            <td class="answer hidden-tiny-down">
                <?= $this->render_partial('index/_last_post.php', compact('entry')) ?>
            </td>

            <td class="actions">
                <?= ActionMenu::get()
                    ->addLink(
                        $controller->url_for("index/index/{$entry['last_posting']['topic_id']}#{$entry['last_posting']['topic_id']}"),
                        _('Zur letzten Antwort'),
                        Icon::create('forum'),
                        ['class' => 'hidden-small-up']
                    )
                    // Make thread sticky/unsticky
                    ->conditionAll(ForumPerm::has('make_sticky', $seminar_id) && $constraint['depth'] >= 1)
                    ->condition(!$entry['sticky'])
                    ->addLink(
                        $controller->url_for('index/make_sticky', $entry['topic_id'], $constraint['topic_id'], 0),
                        _('Thema hervorheben'),
                        Icon::create('staple'),
                        ['id' => "stickyButton-{$entry['topic_id']}"]
                    )
                    ->condition($entry['sticky'])
                    ->addLink(
                        $controller->url_for('index/make_unsticky', $entry['topic_id'], $constraint['topic_id'], 0),
                        _('Hervorhebung aufheben'),
                        Icon::create('staple'),
                        ['id' => "stickyButton-{$entry['topic_id']}"]
                    )
                    ->conditionAll(null)
                    // Move thread
                    ->condition(ForumPerm::has('move_thread', $seminar_id))
                    ->addLink(
                        "javascript:STUDIP.Forum.moveThreadDialog('{$entry['topic_id']}');",
                        _('Dieses Thema verschieben'),
                        Icon::create('folder-full+move_right'),
                        ['class' => 'js']
                    )
                    // Open/close thread
                    ->conditionAll(ForumPerm::has('close_thread', $seminar_id) && $constraint['depth'] >= 1)
                    ->condition(!$entry['closed'])
                    ->addLink(
                        $controller->url_for('index/close_thread', $entry['topic_id'], $constraint['topic_id'], ForumHelpers::getPage()),
                        _('Thema schließen'),
                        Icon::create('lock-locked'),
                        [
                            'id'      => "closeButton-{$entry['topic_id']}",
                            'onclick' => "STUDIP.Forum.closeThreadFromOverview('{$entry['topic_id']}', '{$constraint['topic_id']}', " . ForumHelpers::getPage() . "); return false;",
                        ]
                    )
                    ->condition($entry['closed'])
                    ->addLink(
                        $controller->url_for('index/open_thread', $entry['topic_id'], $constraint['topic_id'], ForumHelpers::getPage()),
                        _('Thema öffnen'),
                        Icon::create('lock-unlocked'),
                        [
                            'id'      => "closeButton-{$entry['topic_id']}",
                            'onclick' => "STUDIP.Forum.openThreadFromOverview('{$entry['topic_id']}', '{$constraint['topic_id']}', " . ForumHelpers::getPage() . "); return false;",
                        ]
                    )
                    ->conditionAll(null)
                    // Delete thread
                    ->condition(ForumPerm::has('remove_thread', $seminar))
                    ->addButton(
                        'delete',
                        _('Dieses Thema löschen'),
                        Icon::create('trash'),
                        [
                            'formaction'   => $controller->url_for("index/delete_entry/{$entry['topic_id']}"),
                            'data-confirm' => sprintf(
                                _('Sind sie sicher dass Sie den Eintrag %s löschen möchten?'),
                                $entry['name']
                            )
                        ]
                    )
                ?>

            <? if (ForumPerm::has('move_thread', $seminar_id)) : ?>
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
            </td>
        </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endforeach ?>
</form>
