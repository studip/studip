<tr data-area-id="<?= $entry['topic_id'] ?>" <?= (ForumPerm::has('sort_area', $seminar_id)) ? 'class="movable"' : '' ?>>
    <td class="icon">
    <? if (ForumPerm::has('sort_area', $seminar_id)) : ?>
        <img src="<?= $picturepath ?>/anfasser_48.png" class="handle js">
    <? endif ?>

        <a href="<?= $controller->link_for("index/index/{$entry['topic_id']}#{$entry['topic_id']}") ?>">
        <? if ($entry['chdate'] >= $visitdate && $entry['user_id'] !== $GLOBALS['user']->id): ?>
            <?= Icon::create('forum+new', Icon::ROLE_ATTENTION)->asImg([
                'title' => _('Dieser Eintrag ist neu!'),
                'style' => 'margin-bottom: 15px',
            ]) ?>
        <? else : ?>
            <? $num_postings = ForumVisit::getCount($entry['topic_id'], $visitdate) ?>
            <?= Icon::create('forum', $num_postings > 0 ? Icon::ROLE_ATTENTION : Icon::ROLE_INFO)->asImg([
                'title' => htmlReady(ForumHelpers::getVisitText($num_postings, $entry['topic_id'], $constraint['depth'])),
                'style' => 'margin-bottom: 15px;',
            ]) ?>
        <? endif ?>
        </a>
    </td>
    <td class="areaentry">
        <div style="position: relative;<?= Request::get('edit_area') == $entry['topic_id'] ? 'height: auto;' : '' ?>">

            <span class="areadata" <?= Request::get('edit_area') != $entry['topic_id'] ? '' : 'style="display: none;"' ?>>
                <a href="<?= $controller->link_for("index/index/{$entry['topic_id']}#{$entry['topic_id']}") ?>">
                    <span class="areaname"><?= htmlReady($entry['name_raw']) ?></span>
                </a>
                <div class="areacontent" data-content="<?= htmlReady($entry['content_raw']) ?>">
                    <? $description = ForumEntry::killFormat(ForumEntry::killEdit($entry['content_raw'])) ?>
                    <?= htmlReady(mila($description, 150)) ?>
                </div>
            </span>


            <? if (ForumPerm::has('edit_area', $seminar_id) && Request::get('edit_area') == $entry['topic_id']) : ?>
            <span style="text-align: center;">
                <div style="width: 90%">
                    <?= $this->render_partial('area/_edit_area_form', compact('entry')) ?>
                </div>
            </span>
            <? endif ?>
        </div>
    </td>

    <td class="postings">
        <?= number_format(max($entry['num_postings'] - 1, 0), 0, ',', '.') ?>
    </td>

    <td class="answer hidden-small-down">
        <?= $this->render_partial('index/_last_post.php', compact('entry')) ?>
    </td>

    <td class="actions">
        <?= ActionMenu::get()->addLink(
            $controller->url_for("index/index/{$entry['last_posting']['topic_id']}#{$entry['last_posting']['topic_id']}"),
            _('Zur letzten Antwort'),
            Icon::create('forum'),
            is_array($entry['last_posting']) ? ['class' => 'hidden-small-up'] : ['disabled' => '']
        )->condition(ForumPerm::has('edit_area', $seminar_id) && $issue_id = ForumIssue::getIssueIdForThread($entry['topic_id']))
        ->addLink(
            URLHelper::getURL("dispatch.php/course/topics/edit/{$issue_id}"),
            _('Zum Ablaufplan'),
            Icon::create('info-circle', Icon::ROLE_STATUS_RED),
            ['title' => _('Dieser Bereich ist einem Thema zugeordnet und kann hier nicht editiert werden. Die Angaben können im Ablaufplan angepasst werden.')]
        )->condition(ForumPerm::has('edit_area', $seminar_id) && !$issue_id)
        ->addLink(
            $controller->url_for('index', ['edit_area' => $entry['topic_id']]),
            _('Name/Beschreibung des Bereichs ändern'),
            Icon::create('edit'),
            [
                'class'   => 'edit-area',
                'onclick' => "STUDIP.Forum.editArea('{$entry['topic_id']}');return false;",
            ]
        )->condition(ForumPerm::has('remove_area', $seminar_id))
        ->addLink(
            $controller->url_for("index/delete_entry/{$entry['topic_id']}"),
            _('Bereich mitsamt allen Einträgen löschen!'),
            Icon::create('trash'),
            [
                'class'   => 'delete-area',
                'onclick' => "STUDIP.Forum.deleteArea(this, '{$entry['topic_id']}'); return false;",
            ]
        ) ?>
    </td>

</tr>
