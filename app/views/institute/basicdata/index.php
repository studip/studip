<?= $question ?>
<form method="post" name="edit" action="<?= $controller->url_for('institute/basicdata/store/' . $i_view) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Verwaltung der Einrichtungsgrunddaten') ?></legend>

        <label class="col-6">
            <span class="required"><?= _('Name') ?></span>
            <?= I18N::input('Name', $institute->Name, LockRules::Check($institute->id, 'name') ? ['readonly' => true, 'disabled' => true] : ['required' => true]); ?>
        </label>

        <label class="col-3">
            <?= _('Fakultät') ?>

        <? if (count($institute->sub_institutes) > 0): ?>
            <br>
            <small style="font-weight: normal;">
                <?= _('Diese Einrichtung hat den Status einer Fakultät.') ?><br>
                <?= sprintf(_('Es wurden bereits %u andere Einrichtungen zugeordnet.'), count($institute->sub_institutes)) ?>
            </small>
            <input type="hidden" name="Fakultaet" id="Fakultaet" value="<?= $institute->id ?>">
        <? else: ?>
            <select <?= !$may_edit_faculty ? 'readonly disabled' : '' ?> name="Fakultaet" id="Fakultaet">
            <? if ($GLOBALS['perm']->have_perm('root')): ?>
                <option value="<?= $institute->id ?>" <?= ($institute->fakultaets_id == Request::option('Fakultaet', $institute->id)) ? 'selected' : '' ?>>
                    <?= _('Diese Einrichtung hat den Status einer Fakultät.') ?>
                </option>
            <? endif; ?>
            <? if ($may_edit_faculty): ?>
                <? foreach ($faculties as $faculty): ?>
                    <option value="<?= $faculty->id ?>"
                            <? if ($faculty->id === $institute->id): echo 'disabled'; ?>
                            <? elseif ($faculty->id == Request::option('Fakultaet', $institute->fakultaets_id)): echo 'selected'; ?>
                            <? endif; ?>>
                        <?= htmlReady($faculty->name) ?>
                    </option>
                <? endforeach; ?>
            <? else: ?>
                <option value="<?= $institute->fakultaets_id ?>">
                    <?= htmlReady($institute->faculty->name) ?>
                </option>
            <? endif; ?>
            </select>
        <? endif; ?>
        </label>

        <label class="col-3">
            <?= _('Bezeichnung') ?>

            <select name="type" id="type" <?= LockRules::Check($institute->id, 'type') ? 'readonly disabled' : '' ?> >
            <? foreach ($GLOBALS['INST_TYPE'] as $i => $inst_type): ?>
                <option value="<?= $i ?>" <?= (Request::int('type', $institute->type) == $i) ? 'selected' : '' ?>>
                    <?= htmlReady($inst_type['name']) ?>
                </option>
            <? endforeach; ?>
           </select>
        </label>

        <label class="col-3">
            <?= _('Straße') ?>
            <input type="text" <?= LockRules::Check($institute->id, 'strasse') ? 'readonly disabled' : '' ?> id="strasse" name="strasse"
                   value="<?= htmlReady(Request::get('strasse', $institute->strasse)) ?>">
        </label>

        <label class="col-3">
            <?= _('Ort') ?>
            <input type="text" <?= LockRules::Check($institute->id, 'plz') ? 'readonly disabled' : '' ?> id="plz" name="plz"
                   value="<?= htmlReady(Request::get('plz', $institute->plz)) ?>">
        </label>

        <label class="col-3">
            <?= _('Telefonnummer') ?>
            <input type="text" <?= LockRules::Check($institute->id, 'telefon') ? 'readonly disabled' : '' ?> id="telefon" name="telefon"
                   value="<?= htmlReady(Request::get('telefon', $institute->telefon)) ?>">
        </label>

        <label class="col-3">
            <?= _('Faxnummer') ?>
            <input type="text" <?= LockRules::Check($institute->id, 'fax') ? 'readonly disabled' : '' ?> id="fax" name="fax"
                   value="<?= htmlReady(Request::get('fax', $institute->fax)) ?>">
        </label>

        <label class="col-3">
            <?= _('E-Mail-Adresse') ?>
            <input type="text" <?= LockRules::Check($institute->id, 'email') ? 'readonly disabled' : '' ?> id="email" name="email"
                   value="<?= htmlReady(Request::get('email', $institute->email)) ?>">
        </label>

        <label class="col-3">
            <?= _('Homepage') ?>
            <input type="text" <?= LockRules::Check($institute->id, 'url') ? 'readonly disabled' : '' ?> id="home" name="home"
                   value="<?= htmlReady(Request::get('home', $institute->url)) ?>">
        </label>

    <? if (get_config('LITERATURE_ENABLE') && $institute->is_fak): // choose preferred lit plugin ?>
        <label class="col-3">
            <?= _('Bevorzugter Bibliothekskatalog') ?>
            <select id="lit_plugin_name" name="lit_plugin_name">
            <? foreach (StudipLitSearch::GetAvailablePlugins() as $name => $title): ?>
                <option value="<?= $name ?>" <?= ($name == Request::get('lit_plugin_name', $institute->lit_plugin_name)) ? 'selected' : '' ?>>
                    <?= htmlReady($title) ?>
               </option>
            <? endforeach; ?>
            </select>
        </label>
    <? endif; ?>

    <? if ($GLOBALS['perm']->have_perm('root')): // Select lockrule to apply ?>
        <label class="col-3">
            <?= _('Sperrebene') ?>
            <select id="lock_rule" name="lock_rule">
                <option value="">&nbsp;</option>
            <? foreach (LockRule::findAllByType('inst') as $rule): ?>
                <option value="<?= $rule->getId() ?>" <?= ($rule->getId() == Request::option('lock_rule', $institute->lock_rule)) ? 'selected' : '' ?>>
                    <?= htmlReady($rule->name) ?>
                </option>
            <? endforeach;?>
            </select>
        </label>
    <? endif; ?>

    <? foreach ($datafields as $key => $datafield): ?>
        <div class="col-3" style="vertical-align: top">
            <?= $datafield['value'] ?>
        </div>
    <? endforeach; ?>
    </fieldset>

    <footer>
    <? if ($i_view != 'new' && !$institute->isNew()): ?>
        <input type="hidden" name="i_id" value="<?= $institute->id ?>">
        <?= Studip\Button::createAccept(_('Übernehmen'), 'i_edit') ?>
        <?= Studip\LinkButton::create(_('Löschen'),
                                      $controller->url_for('institute/basicdata/index/' . $i_view, ['i_trykill' => 1]),
                                      !$may_delete ? ['disabled' => ''] : []) ?>
        <? if (!$may_delete && mb_strlen($reason_txt) > 0): ?>
            <?= tooltipIcon($reason_txt, true) ?>
        <? endif; ?>
    <? else: ?>
        <?= Studip\Button::create(_('Anlegen'), 'create') ?>
    <? endif; ?>
       <input type="hidden" name="i_view" value="<?= $i_view ?>">
    </footer>
</form>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/institute-sidebar.png');

if (!$institute->isNew()) {
    $widget = new ActionsWidget();
    $widget->addLink(
        _('Infobild ändern'),
        URLHelper::getURL('dispatch.php/avatar/update/institute/' . $institute->id),
        Icon::create('edit')
    )->asDialog();
    if (InstituteAvatar::getAvatar($institute->id)->is_customized()) {
        $widget->addLink(
            _('Infobild löschen'),
            URLHelper::getURL('dispatch.php/avatar/delete/institute/' . $institute->id),
            Icon::create('trash')
        );
    }
    $sidebar->addWidget($widget);
}
