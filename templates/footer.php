<!-- Beginn Footer -->
<div id="layout_footer">
<? if (is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody') : ?>
    <div id="footer">
        <? printf(_('Sie sind angemeldet als %s (%s)'),
                  htmlReady($GLOBALS['user']->username),
                  htmlReady($GLOBALS['user']->perms)) ?>
        |
        <?= strftime('%x, %X') ?>
    <? if (Studip\ENV === 'development'): ?>
        [
        <? if (DBManager::get('studip') === DBManager::get('studip-slave')): ?>
            <?= sprintf('%u db queries', DBManager::get('studip')->query_count) ?>
        <? else: ?>
            <?= sprintf(
                'M%u/S%u = %u db queries',
                DBManager::get('studip')->query_count,
                DBManager::get('studip-slave')->query_count,
                DBManager::get('studip')->query_count + DBManager::get('studip-slave')->query_count
            ) ?>
        <? endif; ?>
            /
            <?= relsize(memory_get_peak_usage(true), false) ?> mem
            /
            <?= sprintf('%.5f sec', microtime(true) - $GLOBALS['STUDIP_STARTUP_TIME']) ?>
        ]
        <? if ($GLOBALS['DEBUG_ALL_DB_QUERIES']) : ?>
            <a href="" onClick="jQuery('#all_db_queries').toggle(); return false;">
                <?= Icon::create("code", "info_alt")->asImg(16, ['class' => "text-bottom"]) ?>
            </a>
        <? endif ?>
    <? endif; ?>
    </div>
<? endif; ?>

<? if (Navigation::hasItem('/footer')) : ?>
    <ul>
    <? foreach (Navigation::getItem('/footer') as $nav): ?>
        <? if ($nav->isVisible()): ?>
            <li>
            <a
            <? if (is_internal_url($url = $nav->getURL())) : ?>
                href="<?= URLHelper::getLink($url, $header_template->link_params) ?>"
            <? else: ?>
                href="<?= htmlReady($url) ?>" target="_blank" rel="noopener noreferrer"
            <? endif ?>
                <?= arrayToHtmlAttributes($nav->getLinkAttributes()) ?>
            ><?= htmlReady($nav->getTitle()) ?></a>
            </li>
        <? endif; ?>
    <? endforeach; ?>
    </ul>
<? endif; ?>
</div>
<?= $this->render_partial('debug/db-log.php') ?>
<?= $this->render_partial('responsive-navigation.php') ?>
<!-- Ende Footer -->
