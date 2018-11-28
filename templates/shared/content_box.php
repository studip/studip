<? if ($content_for_layout != ''): ?>
    <? if (!isset($admin_title)) $admin_title = _('Administration') ?>
    <section class="contentbox">
        <header>
            <h1>
                <? if (isset($icon_url)): ?>
                    <?= Assets::img($icon_url) ?>
                <? endif ?>
                <?= htmlReady($title) ?>
            </h1>
        <? if (isset($admin_url)): ?>
            <nav>
                <a href="<?= URLHelper::getLink($admin_url) ?>" title="<?= htmlReady($admin_title) ?>">
                    <?= Icon::create('admin')->asImg(tooltip2(htmlReady($admin_title))) ?>
                </a>
            </nav>
        <? endif; ?>
        </header>
        <section>
            <?= $content_for_layout ?>
        </section>
    </section>
<? endif;
