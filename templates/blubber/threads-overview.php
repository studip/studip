<div class="sidebar-widget blubber_threads_widget"
     data-threads_data="<?= htmlReady(json_encode($json)) ?>">
    <div class="sidebar-widget-header">
        <div class="actions">
            <? if ($with_composer) : ?>
                <a href="<?= URLHelper::getLink("dispatch.php/blubber/compose") ?>" data-dialog>
                    <?= Icon::create("add", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
            <? endif ?>
        </div>
        <?= _("Konversationen") ?>
    </div>
    <div class="sidebar-widget-content">
        <blubber-thread-widget :threads="threads" :active_thread="active_thread" :more_down="threads_more_down"></blubber-thread-widget>
    </div>
</div>