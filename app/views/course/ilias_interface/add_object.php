<form class="default" action="<?= $controller->url_for('course/ilias_interface/add_object/'.$mode.'/'.$ilias_index) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
<? if (!$ilias_index) : ?>
    <label>
        <span class="required"><?= _('ILIAS-Installation auswählen') ?></span>
        <select name="ilias_index" required>
            <option></option>
        <? foreach ($ilias_list as $ilias_list_index => $ilias) : ?>
            <option value="<?= htmlReady($ilias_list_index) ?>">
                <?= htmlReady($ilias->getName()) ?>
            </option>
        <? endforeach ?>
        </select>
    </label>
<? elseif ($mode === 'search' && count($ilias_modules) === 0) : ?>
    <label>
        <span><?= _('Suche nach Lernobjekten') ?></span>
        <input type="text" name="ilias_search" value="<?= htmlReady($ilias_search) ?>"
               size="50" maxlength="50" required>
    </label>
<? elseif ($mode === 'new_course') : ?>
    <div>
        <input type="hidden" name="cmd" value="add_course">
        <?=sprintf(_('Sie können nun einen leeren Kurs in der %s-Installation anlegen.'), $ilias->getName())?>
    </div>
<? elseif ($mode === 'assign_course') : ?>
    <div>
        <input type="hidden" name="cmd" value="assign_course">
        <label>
            <span><?= _('Veranstaltung wählen') ?></span>
            <select name="ilias_course_id" required>
                <option></option>
            <? foreach ($studip_course_list as $ilias_course_id => $studip_course_name) : ?>
                <option value="<?= htmlReady($ilias_course_id) ?>">
                    <?= htmlReady($studip_course_name) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
    </div>
<? elseif ($mode === 'assign_own_course') : ?>
    <div>
        <input type="hidden" name="cmd" value="assign_course">
    <? if ($submit_text) : ?>
        <label>
            <span><?= _('ILIAS-Kurs wählen') ?></span>
            <select name="ilias_course_id" required>
                <option></option>
            <? foreach ($studip_course_list as $ilias_course_id => $studip_course_name) : ?>
                <option value="<?= htmlReady($ilias_course_id) ?>">
                    <?= htmlReady($studip_course_name) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
    <? else : ?>
        <?= sprintf(
            _('Es wurden keine Kurse in der %s-Installation gefunden, in denen Sie als Kursadministrator/-in eingetragen sind.'),
            htmlReady($ilias->getName())
        ) ?>
    <? endif ?>
    </div>
<? elseif ($mode === 'search' || $mode === 'my_modules') : ?>
    <table class="default">
        <caption>
        <? if ($mode === 'search') : ?>
            <?= _('Gefundene Lernobjekte') ?>
        <? elseif ($mode === 'my_modules') : ?>
            <?= _('Meine Lernobjekte') ?>
        <? endif ?>
        </caption>
        <colgroup>
            <col style="width: 5%">
            <col style="width: 65%">
            <col style="width: 20%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <th></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Typ') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </thead>
        <tbody>
        <? foreach ($ilias_modules as $module_id => $module) : ?>
            <tr>
                <td>
                    <?= Icon::create('learnmodule')->asImg([
                        'title' => $module->getModuleTypeName(),
                    ]) ?>
                </td>
                <td>
                    <a href="<?= $controller->link_for($module->getRoute('view_course'), compact('ilias_search', 'mode')) ?>" <?= $dialog ? 'data-dialog=""' : ''?>>
                        <?= htmlReady($module->getTitle()) ?>
                    </a>
                </td>
                <td><?= $module->getModuleTypeName() ?></td>
                <td class="actions">
                    <?= ActionMenu::get()->addButton(
                        'view',
                        _('Info'),
                        Icon::create('info-circle'),
                        [
                            'title'        => _('Info'),
                            'formaction'   => $controller->url_for($module->getRoute('view_course') .'?ilias_search='.htmlReady($ilias_search)),
                            'data-dialog'  => ''
                        ]
                    )->condition($edit_permission)->addButton(
                        'add',
                        _('Hinzufügen'),
                        Icon::create('add'),
                        [
                            'title'        => _('Hinzufügen'),
                            'formaction'   => $controller->url_for($module->getRoute('add')),
                            'data-dialog'  => ''
                        ]
                    )->render() ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
    <? endif ?>
    <footer data-dialog-button>
    <? if ($ilias->isActive() && $submit_text) : ?>
        <?= Studip\Button::create($submit_text, 'submit', ($dialog && $keep_dialog) ? ['data-dialog' => 'size=auto;reload-on-close'] : []) ?>
    <? endif ?>
        <?= Studip\Button::createCancel(_('Schließen'), 'cancel', $dialog ? ['data-dialog' => 'close'] : []) ?>
    </footer>
</form>
