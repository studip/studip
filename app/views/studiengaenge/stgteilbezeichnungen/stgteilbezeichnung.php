<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = MvvPerm::get($stgteilbezeichnung) ?>
<h3>
    <? if ($stgteilbezeichnung->isNew()) : ?>
    <?= _('Neue Studiengangteil-Bezeichnung') ?>
    <? else : ?>
    <?= sprintf(_('Studiengangteil-Bezeichnung: %s'), htmlReady($stgteilbezeichnung->name)) ?>
    <? endif; ?>
</h3>
<form class="mvv-form default"
      action="<?= $controller->url_for('studiengaenge/stgteilbezeichnungen/store'. ($stgteilbezeichnung->getId() ? '/' . $stgteilbezeichnung->getId() : '')) ?>"
      method="post"<?= Request::isXhr() ? ' data-dialog' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label><?= _('Studiengangteil-Bezeichnung') ?>
            <?= MvvI18N::input('name', $stgteilbezeichnung->name, ['maxlength' => '100', 'required' => ''])->checkPermission($stgteilbezeichnung) ?>
        </label>
        <label><?= _('Kurzname') ?>
            <?= MvvI18N::input('name_kurz', $stgteilbezeichnung->name_kurz, ['maxlength' => '20'])->checkPermission($stgteilbezeichnung) ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($stgteilbezeichnung->isNew()) : ?>
        <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Studiengangteil-Bezeichnung anlegen'))) ?>
        <? else : ?>
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('studiengaenge/stgteilbezeichnungen/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>
