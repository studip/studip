<? if ($section == 'index' || !$section) : ?>
<div id="tutorBreadcrumb">
    <? $path = ForumEntry::getPathToPosting($topic_id) ?>
    <a href="<?= PluginEngine::getURL('coreforum/index') ?>" title="<?= _('Übersicht') ?>">
        <?= Icon::create('forum', 'clickable') ?>
    </a>

    <? foreach ($path as $path_part) : ?>
        <? if (sizeof($path) > 1) :?>/<? endif ?>
        <a href="<?= PluginEngine::getLink('coreforum/index/index/' . $path_part['id']) ?>">
            <?= htmlReady(ForumEntry::killFormat($path_part['name'])) ?>
        </a>
        <? $first = false ?>
    <? endforeach ?>
</div>
<? endif ?>
