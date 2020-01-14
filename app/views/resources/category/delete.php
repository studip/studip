<? if ($show_form): ?>
    <?= MessageBox::warning(
        _('Soll die folgende Ressourcenkategorie wirklich gelöscht werden?')
    ) ?>
    <form method="post" data-dialog="reload-on-close" class="default"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/category/delete/' . $category->id) ?>">
        <fieldset>
            <legend><?= _('Informationen') ?></legend>
            <dl>
                <dt><?= _('Name') ?></dt>
                <dd><?= htmlReady($category->name) ?></dd>
                <dt><?= _('Zugehörige Klasse') ?></dt>
                <dd><?= htmlReady($category->class_name) ?></dd>
            </dl>
        </fieldset>
        <? if ($category_has_resources): ?>
            <fieldset>
                <legend><?= _('Zuweisen von Ressourcen') ?></legend>
                <p><?= _('Die zu dieser Kategorie gehörenden Ressourcen müssen einer anderen Kategorie zugewiesen oder gelöscht werden!') ?></p>
                <label>
                    <input type="radio" name="resource_handling" value="reassign"
                           <?= $resource_handling == 'reassign' ? 'checked="checked"' : '' ?>
                           data-activates="select#new_resource_category_select">
                    <?= _('Ressourcen der folgenden Kategorie zuordnen:') ?>
                    <select id="new_resource_category_select"
                            name="new_category_id"
                            <?= $resource_handling == 'reassign' ? '' : 'disabled="disabled"' ?>>
                        <option value="" <?= !$new_category_id ? 'selected="selected"' : '' ?>>
                            <?= _('bitte wählen') ?>
                        </option>
                        <? foreach ($alternative_categories as $alt_cat): ?>
                            <option value="<?= htmlReady($alt_cat->id) ?>"
                                    <?= $new_category_id == $alt_cat->id ? 'selected="selected"' : '' ?>>
                                <?= htmlReady($alt_cat->name) ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </label>
                <label>
                    <input type="radio" name="resource_handling" value="delete"
                           data-deactivates="select#new_resource_category_select"
                           <?= $resource_handling == 'delete' ? 'checked="checked"' : '' ?>>
                    <?= _('Ressourcen unwiderruflich löschen') ?>
                </label>
            </fieldset>
        <? endif ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Löschen'), 'confirmed') ?>
        </div>
    </form>
<? endif ?>
