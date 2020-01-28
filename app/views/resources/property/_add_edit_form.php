<label>
    <?= _('Name') ?>
    <input type="text" value="<?= htmlReady($name) ?>" name="name"
        <?= $property->system ? 'readonly="readonly"' : '' ?>>
</label>
<label>
    <?= _('Beschreibung') ?>
    <?= I18N::textarea('description', $description) ?>
</label>
<label>
    <?= _('Typ') ?>
    <select name="type" class="size-s"
        <?= $property->system ? 'disabled="disabled"' : '' ?>>
        <? foreach ($defined_types as $defined_type): ?>
            <option value="<?= htmlReady($defined_type) ?>"
                <?= $defined_type == $type
                    ? 'selected="selected"'
                    : '' ?>>
                <?= htmlReady($defined_type) ?>
            </option>
        <? endforeach ?>
    </select>
</label>
<label>
    <?= _('Minimale Rechtestufe für Änderungen') ?>
    <select name="write_permission_level" class="size-l"
        <?= $property->system ? 'disabled="disabled"' : '' ?>>
        <? foreach(['user', 'autor', 'tutur', 'admin'] as $level) : ?>
            <option value="<?= $level?>"
                <?= $write_permission_level === $level ? 'selected="selected"' : '' ?>>
                <?= $level?>
            </option>
        <? endforeach ?>
        <option value="admin-global"
            <?= $write_permission_level == 'admin-global' ? 'selected="selected"' : '' ?>>
            <?= _('Globaler Raumadmin') ?>
        </option>
    </select>
</label>
<label>
    <?= _('Mögliche Werte') ?>
    <input type="text" name="options" value="<?= htmlReady($options) ?>"
        <?= $property->system ? 'readonly="readonly"' : '' ?>>
</label>
<label>
    <?= _('Angezeigter Name') ?>
    <?= I18N::input('display_name', $display_name) ?>
</label>
<input type="checkbox" name="searchable" id="searchable" class="studip-checkbox"
    <?= $searchable ? 'checked="checked"' : '' ?>
    <?= $property->system ? 'disabled="disabled"' : '' ?>>
<label for="searchable">
    <?= _('Diese Eigenschaft kann zur Suche genutzt werden.') ?>
</label>
<input type="checkbox" value="1" name="info_label" id="info_label" class="studip-checkbox"
    <?= $info_label ? 'checked="checked"' : '' ?>>
<label for="info_label">
    <?= _('Diese Eigenschaft soll im Info-Icon zu einem Raum angezeigt werden.') ?>
</label>
<input type="checkbox" name="range_search" value="1" class="studip-checkbox" id="range_search"
    <?= $range_search
        ? 'checked="checked"'
        : '' ?>
    <?= $property->system ? 'disabled="disabled"' : '' ?>>
<label for="range_search">
    <?= _('Suchkriterium mit Intervall') ?>
</label>
