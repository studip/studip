<div class="edit-widgetcontainer">
    <div class="start-widgetcontainer">
        <ul class="portal-widget-list">
            <? foreach ($initial_widgets[0] as $widget_id) : ?>
                <? $widget = $widgets[$widget_id]; unset($widgets[$widget_id]) ?>
                <li class="studip-widget-wrapper" id="<?= $widget->getPluginId() ?>">
                    <div class="ui-widget-content studip-widget">
                        <div class="ui-widget_head widget-header">
                            <span>
                                <?= htmlReady($widget->getPluginName()) ?>
                            </span>
                        </div>
                    </div>
                </li>
            <? endforeach; ?>
        </ul>

        <ul class="portal-widget-list">
            <? foreach ($initial_widgets[1] as $widget_id) : ?>
                <? $widget = $widgets[$widget_id]; unset($widgets[$widget_id]) ?>
                <li class="studip-widget-wrapper" id="<?= $widget->getPluginId() ?>">
                    <div class="ui-widget-content studip-widget">
                        <div class="ui-widget_head widget-header">
                            <span>
                                <?= htmlReady($widget->getPluginName()) ?>
                            </span>
                        </div>
                    </div>
                </li>
            <? endforeach; ?>
        </ul>
    </div>

    <h2><?= _('Nicht standardmäßig aktivierte Widgets') ?></h2>
    <div class="available-widgets">
        <ul class="portal-widget-list" style="clear: both;">
        <? foreach ($widgets as $widget) : ?>
            <li class="studip-widget-wrapper" id="<?= $widget->getPluginId() ?>">
                <div class="ui-widget-content studip-widget">
                    <div class="ui-widget_head widget-header">
                        <span>
                            <?= htmlReady($widget->getPluginName()) ?>
                        </span>
                    </div>
                </div>
            </li>
        <? endforeach; ?>
        </ul>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        STUDIP.startpage.init_edit('<?= $permission ?>');
    })
}(jQuery));
</script>