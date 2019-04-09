<?php
use Studip\Button, Studip\LinkButton;
?>

<form action="<?= $controller->url_for('admin/datafields/new/') ?>" method="post"
      class="default" <? if (Request::isXhr()) echo 'data-dialog'; ?>>

    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Verwaltung von generischen Datenfeldern') ?></legend>

        <label>
            <?= _('Datenfeldtyp:') ?>

            <select name="datafield_typ">
            <? foreach ($allclasses as $key => $class): ?>
                 <option value="<?= $key ?>" <? if ($key === $class_filter) echo 'selected'; ?>>
                     <?= htmlReady($class) ?>
                 </option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::create(_('Auswählen'), 'auswaehlen', ['title' => _('Datenfeld auswählen')])?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/datafields', compact('class_filter'))) ?>
    </footer>
</form>
