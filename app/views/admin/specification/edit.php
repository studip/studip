<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if (isset($flash['error'])) : ?>
    <?= MessageBox::error($flash['error'], $flash['error_detail']) ?>
<? elseif (isset($flash['info'])): ?>
    <?= MessageBox::info($flash['info']) ?>
<? endif ?>

<form action="<?= $controller->url_for('admin/specification/store' . ($rule ? '/' . $rule['lock_id'] : '')) ?>"
      method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <? if ($rule) : ?>
                <?= sprintf(_('Regel "%s" editieren'), htmlReady($rule['name'])) ?>
            <? else : ?>
                <?= _('Eine neue Regel definieren') ?>
            <? endif ?>
        </legend>
        <label>
            <span class="required">
                <?= _('Name der Regel:') ?>
            </span>
            <input type="text" name="rulename" value="<?= htmlReady(Request::get('rulename', $rule['name'])) ?>"
                   required="required">
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <textarea cols="60" rows="5"
                      name="description"><?= htmlReady(Request::get('description', $rule['description'])) ?></textarea>
        </label>
    </fieldset>
    <? if (count($entries_semdata) > 0) : ?>
        <fieldset>
            <legend>
                <?= _('Zusatzinformationen') ?>
            </legend>
            <? foreach ($entries_semdata as $id => $entry) : ?>
                <?= $this->render_partial('admin/specification/_field', array_merge(
                    compact('rule'),
                    ['id' => $entry->datafield_id, 'name' => $entry->name],
                    ['required' => true, 'institution' => $entry->institution]
                )) ?>
            <? endforeach ?>
        </fieldset>
    <? endif ?>

    <? if (count($semFields) > 0) : ?>
        <fieldset>
            <legend>
                <?= _('Veranstaltungsinformationen') ?>
            </legend>
            <? foreach ($semFields as $id => $name) : ?>
                <?= $this->render_partial('admin/specification/_field', compact('rule', 'id', 'name')) ?>
            <? endforeach ?>
        </fieldset>
    <? endif ?>

    <? if (count($entries_user) > 0) : ?>
        <fieldset>
            <legend>
                <?= _('Personenbezogene Informationen') ?>
            </legend>
            <? foreach ($entries_user as $id => $entry) : ?>
                <?= $this->render_partial('admin/specification/_field',
                        array_merge(compact('rule'), ['id' => $entry->datafield_id, 'name' => $entry->name])) ?>
            <? endforeach ?>
        </fieldset>
    <? endif ?>
    <footer>
        <? if ($rule) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', ['title' => _('Änderungen übernehmen')]) ?>
        <? else : ?>
            <?= Button::createAccept(_('Erstellen'), 'erstellen', ['title' => _('Neue Regel erstellen')]) ?>
        <? endif ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/specification'), ['title' => _('Zurück zur Übersicht')]) ?>
    </footer>
</form>

<?
$sidebar = Sidebar::Get();
$sidebar->setImage('sidebar/admin-sidebar.png');
$sidebar->setTitle(_('Zusatzangaben'));
if ($GLOBALS['perm']->have_perm('root')) {
    $actions = new ActionsWidget();
    $actions->addLink(_('Datenfelder bearbeiten'), URLHelper::getLink('dispatch.php/admin/datafields'), Icon::create('add', 'clickable'));
    $sidebar->addWidget($actions);
}
?>
