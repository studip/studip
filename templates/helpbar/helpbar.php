<div class="helpbar-container">
    <input type="checkbox" id="helpbar-sticky" <? if ($open) echo 'checked'; ?>>
    <div class="helpbar">
        <h2 class="helpbar-title">
            <label for="helpbar-sticky">
                <?= _('Tipps & Hilfe') ?>
            </label>
        </h2>
        <ul class="helpbar-widgets">
        <? foreach ($widgets as $index => $widget): ?>
            <li>
            <? if ($widget->icon): ?>
                <?= is_string($widget->icon) ? Assets::img($widget->icon, ['class' => 'helpbar-widget-icon']) : $widget->icon->asImg(['class' => 'helpbar-widget-icon']) ?>
            <? endif; ?>
                <?= $widget->render(['base_class' => 'helpbar'])?>
                <div class="helpbar-widget-admin-icons">
                <? if ($widget->edit_link): ?>
                    <a href="<?=$widget->edit_link?>" data-dialog="size=auto;reload-on-close">
                    <?= Icon::create('edit', 'info_alt')->asImg() ?></a>
                <? endif; ?>
                <? if ($widget->delete_link): ?>
                    <a href="<?=$widget->delete_link?>" data-dialog="size=auto;reload-on-close">
                    <?= Icon::create('trash', 'info_alt')->asImg() ?></a>
                <? endif; ?>
                <? if ($widget->add_link): ?>
                    <a href="<?=$widget->add_link?>" data-dialog="size=auto;reload-on-close">
                    <?= Icon::create('add', 'info_alt')->asImg() ?></a>
                <? endif; ?>
                </div>
            </li>
        <? endforeach; ?>
        </ul>
    </div>
</div>
<? if ($tour_data['active_tour_id']) : ?>
    <script>
        STUDIP.Tour.init('<?=$tour_data['active_tour_id']?>', '<?=$tour_data['active_tour_step_nr']?>')
    </script>
<? endif ?>
