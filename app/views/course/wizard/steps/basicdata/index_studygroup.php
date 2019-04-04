<legend>
    <?= _('Grunddaten') ?>
</legend>

<label class="col-3">
    <span class="required"><?= _('Name') ?></span>
    <input type="text" name="name" id="wizard-name" maxlength="254" value="<?= $values['name'] ?>" required/>
</label>

<? if(count($types) > 1) : ?>
    <label class="col-3">
        <span class="required"><?= _('Typ') ?></span>
        <select name="coursetype" id="wizard-coursetype">
            <?php foreach ($types as $class => $subtypes) : ?>
                <optgroup label="<?= htmlReady($class) ?>">
                    <?php foreach ($subtypes as $type) : ?>
                        <option value="<?= $type['id'] ?>"<?= $type['id'] == $values['coursetype'] ? ' selected="selected"' : '' ?>>
                            <?= htmlReady($type['name']) ?>
                        </option>
                    <?php endforeach ?>
                </optgroup>
            <?php endforeach ?>
        </select>
    </label>
<? else : ?>
    <? $type = array_shift(array_values($types)) ?>
    <input type="hidden" name="coursetype" value="<?= htmlReady($type[0]['id']) ?>">
<? endif ?>


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

<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
    <p style="font-weight: bold;">
      <?= _('Ich habe die eingetragenen Personen darüber informiert, dass in Ihrem Namen eine Studiengruppe angelegt wird und versichere, dass Sie mit folgenden Nutzungsbedingungen einverstandenen sind:') ?>
    </p>
<? endif ?>
<?= formatReady(Config::Get()->STUDYGROUP_TERMS) ?>

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
