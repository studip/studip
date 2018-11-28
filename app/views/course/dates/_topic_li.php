<li data-issue_id="<?= $topic->id ?>" class="topic_<?= $date->id ?>_<?= $topic->id ?>">
    <a href="<?= $controller->url_for("course/topics#{$topic->id}", ['open' => $topic->id]) ?>" class="title">
        <?= Icon::create('topic')->asImg(['class' => 'text-bottom']) ?>
        <?= htmlReady($topic['title']) ?>
    </a>
<? if ($has_access) : ?>
    <a href="#" onClick="STUDIP.Dates.removeTopicFromIcon.call(this); return false;">
        <?= Icon::create('trash')->asImg(['class' => 'text-bottom'] + tooltip2(_('Thema entfernen'))) ?>
    </a>
<? endif ?>
</li>
