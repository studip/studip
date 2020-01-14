<? if ($show_form): ?>
    <form class="default" method="post"
          action="<?= ($mode == 'add')
                    ? URLHelper::getLink('dispatch.php/resources/resource/add')
                    : URLHelper::getLink('dispatch.php/resources/resource/edit/' . $resource->id) ?>"
          data-dialog="reload-on-close">

        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <? if ($mode == 'add'): ?>
                <input type="hidden" name="category_id"
                       value="<?= htmlReady($category->id) ?>">
            <? endif ?>
            <legend><?= _('Grunddaten') ?></legend>
            <label>
                <?= _('Name') ?>
                <input type="text" name="name" value="<?= htmlReady($name) ?>">
            </label>
            <label>
                <?= _('Beschreibungstext') ?>
                <input type="text" name="description" value="<?= htmlReady($description) ?>">
            </label>
            <? if ($GLOBALS['perm']->have_perm('root')): ?>
                <label>
                    <?= _('Elternressource') ?>
                    <?= $parent_search->render() ?>
                </label>
                <label>
                    <?= _('Sortierposition') ?>
                    <input type="text" name="sort_position"
                           value="<?= htmlReady($sort_position) ?>">
                </label>
            <? endif ?>
        </fieldset>
        <fieldset>
            <legend><?= _('Eigenschaften') ?></legend>
            <?= $this->render_partial(
                'resources/resource/_standard_properties_form_part.php',
                [
                    'grouped_defined_properties' => $grouped_defined_properties,
                    'property_data' => $property_data
                ]
            ) ?>
        </fieldset>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'confirmed') ?>
        </div>
    </form>
<? endif ?>
