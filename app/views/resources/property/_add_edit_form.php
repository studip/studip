<label>
    <?= _('Name') ?>
    <input type="text" value="<?= htmlReady($name) ?>" name="name"
           <?= $property->system ? 'readonly="readonly"' : '' ?>>
</label>
<label>
    <?= _('Beschreibung')?>
    <?= I18N::textarea('description', $description) ?>
</label>
<label>
    <?= _('Typ') ?>
    <select name="type"
            <?= $property->system ? 'disabled="disabled"' : '' ?>>
        <? foreach ($defined_types as $defined_type): ?>
            <option value="<?= htmlReady($defined_type)?>"
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
    <select name="write_permission_level"
            <?= $property->system ? 'disabled="disabled"' : '' ?>>
        <option value="user"
                <?= $write_permission_level == 'user' ? 'selected="selected"' : '' ?>>
            user
        </option>
        <option value="autor"
                <?= $write_permission_level == 'autor' ? 'selected="selected"' : '' ?>>
            autor
        </option>
        <option value="tutor"
                <?= $write_permission_level == 'tutor' ? 'selected="selected"' : '' ?>>
            tutor
        </option>
        <option value="admin"
                <?= $write_permission_level == 'admin' ? 'selected="selected"' : '' ?>>
            admin
        </option>
        <option value="admin-global"
                <?= $write_permission_level == 'admin-global' ? 'selected="selected"' : '' ?>>
            <?= _('Globaler Raumadmin') ?>
        </option>
    </select>
</label>
<label>
    <input type="checkbox" name="searchable"
           <?= $searchable ? 'checked="checked"': '' ?>
           <?= $property->system ? 'disabled="disabled"' : '' ?>>
    <?= _('Diese Eigenschaft kann zur Suche genutzt werden.') ?>
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
<label>
    <input type="checkbox" value="1" name="info_label"
           <?= $info_label ? 'checked="checked"' : '' ?>>
    <?= _('Diese Eigenschaft soll im Info-Icon zu einem Raum angezeigt werden.') ?>
</label>
<label>
    <?= _('Suchkriterium mit Intervall') ?>
    <input type="checkbox" name="range_search" value="1"
           <?= $range_search
             ? 'checked="checked"'
             : '' ?>
           <?= $property->system ? 'disabled="disabled"' : '' ?>
</label>
