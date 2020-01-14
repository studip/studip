<section class="sidebar-widget clipboard-widget"
         id="ClipboardWidget_<?= htmlReady($clipboard_widget_id) ?>"
         data-widget_id="<?= htmlReady($clipboard_widget_id) ?>">
    <header class="sidebar-widget-header">
        <?= _('Individuelle Raumgruppen') ?>
    </header>
    <section class="sidebar-widget-content">
        <div id="clipboard-group-container" class="<?= $clipboards ? '' : 'invisible' ?>">
            <?= $this->render_partial(
                'sidebar/clipboard-area.php',
                [
                    'clipboards' => $clipboards,
                    'allowed_item_class' => $allowed_item_class,
                    'empty_clipboard_string' => _('Ziehen Sie Räume in diesen Bereich um die Raumgruppe zu füllen.'),
                    'selected_clipboard_id' => $selected_clipboard_id,
                    'draggable_items' => $draggable_items,
                    'special_item_template' => 'sidebar/room-clipboard-item',
                    'clipboard_widget_id' => $clipboard_widget_id
                ]
                ); ?>

            <? if (!$readonly): ?>
                <ul class="widget-list widget-links invisible">
                <? foreach ($elements as $index => $element): ?>
                    <li id="<?= htmlReady('link-' . md5($element->url)) ?>" <?= $element->icon ? 'style="' . $element->icon->asCSS() .'"' : '' ?>>
                    <a <?= arrayToHtmlAttributes($element->attributes) ?>
                        data-url_path = "<?= htmlReady($element->url) ?>">
                        <?= htmlReady($element->label) ?>
                    </a>
                    </li>
                <? endforeach; ?>
                </ul>

            </div>

            <form class="default new-clipboard-form"
                  action="<?= URLHelper::getLink(
                          'dispatch.php/clipboard/add'
                          )?>"
                  method="post">
                <?= CSRFProtection::tokenTag() ?>
                <input type="hidden" name="allowed_item_class"
                       value="<?= htmlReady($allowed_item_class) ?>">
                <input type="hidden" name="widget_id"
                       value="<?= htmlReady($clipboard_widget_id) ?>">
                <label>
                    <?= _('Raumgruppe hinzufügen') ?>
                    <?= tooltipIcon(_('Geben Sie bitte einen Namen ein und klicken Sie auf das Plus-Symbol um eine neue Raumgruppe zu erstellen.')) ?>
                    <input type="text" name="name" placeholder="<?= _('Name der neuen Raumgruppe') ?>">

                    <?= Icon::create('add', 'clickable',
                        [   'title' => _('Hinzufügen')])->asInput([
                            'name'   => 'save',
                            'id' => 'add-clipboard-button',
                            'class' => 'middle',
                            'disabled' => 'disabled'
                        ]) ?>
                </label>

            </form>
        <? endif ?>
    </section>
</section>
