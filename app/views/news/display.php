<?= $question ?>
<? if ($perm || $news): ?>
<article class="studip">
    <header>
        <h1>
            <?= Icon::create('news', 'info')->asImg() ?>

            <?= _('Ankündigungen') ?>
        </h1>
        <nav>
        <? if ($perm): ?>
            <a href="<?= $controller->link_for('news/edit_news/new/' . $range); ?>" rel="get_dialog">
                <?= Icon::create('add', 'clickable')->asImg(); ?>
            </a>
        <? endif; ?>
        <? if ($perm && get_config('NEWS_RSS_EXPORT_ENABLE')): ?>
            <a data-dialog="size=auto;reload-on-close" title="<?=_('RSS-Feed konfigurieren') ?>" href="<?= $controller->link_for('news/rss_config/' . $range); ?>">
                <?= Icon::create('rss+add')->asImg() ?>
            </a>
        <? endif; ?>
        <? if ($rss_id): ?>
            <a href="<?= URLHelper::getLink('rss.php', ['id' => $rss_id]) ?>">
                <?= Icon::create('rss', 'clickable', ['title' => _('RSS-Feed')])->asImg() ?>
            </a>
        <? endif; ?>
        </nav>
    </header>
    <? foreach ($news as $new): ?>
    <? $is_new = ($new['chdate'] >= object_get_visit($new->id, 'news', false, false))
            && ($new['user_id'] != $GLOBALS['user']->id); ?>
    <article class="studip toggle <?= ContentBoxHelper::classes($new->id, $is_new) ?>" id="<?= $new->id ?>" data-visiturl="<?=URLHelper::getScriptLink('dispatch.php/news/visit')?>">
        <header>
            <h1>
                <a href="<?= ContentBoxHelper::href($new->id, ['contentbox_type' => 'news']) ?>">
                    <?= Icon::create('news', 'clickable')->asImg(); ?>
                    <?= htmlReady($new['topic']); ?>
                </a>
            </h1>
            <nav>
                <?= $this->render_partial('news/_actions.php', ['new' => $new, 'range' => $range]) ?>
            </nav>
        </header>
        <section>
            <article>
                <?= formatReady($new['body']) ?>
            </article>
        </section>
        <?= $this->render_partial('news/_comments.php', ['new' => $new, 'range' => $range]) ?>
    </article>
    <? endforeach; ?>
    <? if (!$news): ?>
    <section>
        <?= _('Es sind keine aktuellen Ankündigungen vorhanden. Um neue Ankündigungen zu erstellen, klicken Sie rechts auf das Plus-Zeichen.') ?>
    </section>
        <? if ($perm && $count_all_news) : ?>
            <footer>
            <a href="<?=URLHelper::getLink('?nshow_all=1')?>"><?=sprintf(_("Abgelaufene und unveröffentlichte Ankündigungen anzeigen (%s)"), $count_all_news)?></a>
            </footer>
        <? endif; ?>
    <? elseif ($perm) : ?>
        <? if ($count_all_news > count($news)) : ?>
            <footer>
                <a href="<?=URLHelper::getLink('?nshow_all=1')?>"><?=sprintf(_("Abgelaufene und unveröffentlichte Ankündigungen anzeigen (%s)"), $count_all_news-count($news))?></a>
            </footer>
            <? elseif ($show_all_news) : ?>
            <footer>
                <a href="<?=URLHelper::getLink('?nshow_all=0')?>"><?=_("Abgelaufene und unveröffentlichte Ankündigungen ausblenden")?></a>
            </footer>
            <? endif ?>
    <? endif; ?>
</article>
<?endif;
