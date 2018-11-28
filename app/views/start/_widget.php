<div class="ui-widget_head widget-header" id="widget-<?= $widget->widget_id ?>">
    <span class="header-options">
        <? if (isset($icons)): ?>
            <? foreach ($icons as $nav): ?>
                <? if ($nav->isVisible(true)): ?>
                    <? $attr = $nav->getLinkAttributes() ?>
                    <a href="<?= URLHelper::getLink($nav->getURL()) ?>"
                        <? foreach ($attr as $key => $value): ?>
                            <? if ($key !== 'src'): ?>
                                <?= $key ?>="<?= htmlReady($value) ?>"
                            <? endif ?>
                        <? endforeach ?>>
                        <?= $nav->getImage() ?>
                    </a>
                <? endif ?>
            <?endforeach ?>
        <? endif ?>

        <? if (isset($admin_url)): ?>
            <a href="<?= URLHelper::getLink($admin_url) ?>">
                <?= Icon::create('admin', 'clickable', ['title' => $admin_title])->asImg() ?>
            </a>
        <? endif ?>

        <a href="<?= $controller->url_for('start/delete/' . $widget->widget_id) ?>">
            <?= Icon::create('decline', 'clickable', ['title' => _('Entfernen')])->asImg() ?>
        </a>
    </span>
    <span id="widgetName<?= $widget->widget_id ?>" class="widget-title">
        <?= htmlReady(isset($title) ? $title : $widget->getPluginName()) ?>
    </span>
</div>
<div id="wid<?=$widget->widget_id?>">
    <?= $content_for_layout ?>
</div>
