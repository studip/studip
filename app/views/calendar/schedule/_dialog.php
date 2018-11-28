<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable ui-dialog-buttons <?= $class ?: 'schedule-dialog' ?>" tabindex="-1" role="dialog" aria-labelledby="ui-id-2" id="schedule_new_entry" style="width: 600px; height: auto; z-index: 1002;">
    <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" style="z-index: 1001">
        <span id="ui-id-2" class="ui-dialog-title"><?= $title ?></span>
        <a class="ui-dialog-titlebar-close ui-corner-all" href="<?= $controller->url_for('calendar/schedule') ?>" role="button">
            <span class="ui-icon ui-icon-closethick">close</span>
        </a>
    </div>

    <div class="ui-widget-content" style="display: block; width: auto; min-height: 0px; height: 100%;" scrolltop="0" scrollleft="0">
        <?= $content_for_layout ?>
    </div>
</div>