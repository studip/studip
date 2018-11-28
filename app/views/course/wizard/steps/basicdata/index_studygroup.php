<legend>
    <?= _('Grunddaten') ?>
</legend>

<label class="col-3">
    <span class="required"><?= _('Name') ?></span>
    <input type="text" name="name" id="wizard-name" maxlength="254" value="<?= $values['name'] ?>" required/>
</label>

<label class="col-3">
    <span class="required"><?= _('Typ') ?></span>
    <select name="coursetype" id="wizard-coursetype">
        <?php foreach ($types as $class => $subtypes) { ?>
            <optgroup label="<?= htmlReady($class) ?>">
                <?php foreach ($subtypes as $type) { ?>
                    <option value="<?= $type['id'] ?>"<?= $type['id'] == $values['coursetype'] ? ' selected="selected"' : '' ?>>
                        <?= htmlReady($type['name']) ?>
                    </option>
                <?php } ?>
            </optgroup>
        <?php } ?>
    </select>
</label>


<label class="col-3">
    <?= _('Beschreibung') ?>
    <textarea name="description" id="wizard-description" rows="4"></textarea>
</label>


<label class="col-3">
    <span class="required"><?= _('Zugang') ?></span>

    <select name="access" id="wizard-access">
        <option value="all"><?= _('offen für alle') ?></option>
        <option value="invite"><?= _('auf Anfrage') ?></option>
        <?php if (Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED) : ?>
            <option value="invisible"><?= _('unsichtbar') ?></option>
        <?php endif ?>
    </select>
</label>


<label><span class="required"><?= _('Nutzungsbedingungen')?></span></label>

<i><?= htmlReady(Config::Get()->STUDYGROUP_TERMS) ?></i>
<br/>

<label>
    <input type="checkbox" name="accept" id="wizard-accept" required>
    <?= _('Einverstanden') ?>
</label>

<input type="hidden" name="institute" value="<?= $values['institute'] ?>"/>
<input type="hidden" name="start_time" value="<?= $values['start_time'] ?>"/>
<input type="hidden" name="studygroup" value="1"/>
<?php foreach ($values['lecturers'] as $id => $assigned) : ?>
    <input type="hidden" name="lecturers[<?= $id ?>]" value="1"/>
<?php endforeach ?>
