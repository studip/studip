<? if (is_array($entry['last_posting'])) : ?>
    <?= _('von') ?>
    <? if ($entry['last_posting']['anonymous']): ?>
        <?= _('Anonym') ?>
    <? endif; ?>
    <? if (!$entry['last_posting']['anonymous'] || $entry['last_posting']['user_id'] == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')): ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $entry['last_posting']['username']]) ?>">
            <?= htmlReady(($temp_user = User::find($entry['last_posting']['user_id'])) ? $temp_user->getFullname() : $entry['last_posting']['user_fullname']) ?>
        </a>
    <? endif; ?>
    <br>
    <?= _('am') ?>
    <?= strftime($time_format_string_short, (int) $entry['last_posting']['date']) ?>
    <a href="<?= $controller->link_for("index/index/{$entry['last_posting']['topic_id']}#{$entry['last_posting']['topic_id']}") ?>">
        <?= Icon::create('link-intern')->asImg([
            'title' => _('Direkt zum Beitrag...'),
        ]) ?>
    </a>
<? else: ?>
<?= _('keine Antworten') ?>
<? endif; ?>
