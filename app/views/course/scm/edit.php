<p>
    <?= _('Hier können Sie eine Seite mit Zusatzinformationen zu Ihrer '
        . 'Veranstaltung gestalten. Sie können Links normal eingeben, diese '
        . 'werden anschließend automatisch als Hyperlinks dargestellt.')
    ?>
</p>

<form action="<?= $controller->edit($scm) ?>" method="post" data-secure class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Informationsseite') ?>
        </legend>
        <label>
            <span class="required"><?= _('Titel') ?></span>
            <?= I18N::input('tab_name', $scm->tab_name, [
                'required'    => '',
                'id'          => 'tab_name',
                'aria-label'  => _('Titel der Informationsseite'),
                'placeholder' => _('Titel der Informationsseite'),
            ]) ?>
        </label>

    <? if ($GLOBALS['SCM_PRESET']): ?>
        <label>
            <?= _('oder wählen Sie hier einen Namen aus:') ?>
            <select name="tab_name_template" data-copy-to="input[name=tab_name]">
                <option value="">- <?= _('Vorlagen') ?> -</option>
            <? foreach ($GLOBALS['SCM_PRESET'] as $template): ?>
                <option><?= htmlReady($template['name']) ?></option>
            <? endforeach; ?>
            </select>
        </label>
    <? endif; ?>

        <label>
            <?= _('Inhalt') ?>
            <?= I18N::textarea('content', $scm->content, [
                'class' => 'add_toolbar wysiwyg size-l',
            ]) ?>
        </label>
    </fieldset>

<? if (!$scm->isNew()): ?>
    <p>
        <?= sprintf(_('Zuletzt geändert von %s am %s'),
                    ObjectdisplayHelper::link($scm->user),
                    strftime('%x, %X', $scm->chdate)) ?>
    </p>
<? endif; ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
    <? if ($first_entry): ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getLink('seminar_main.php')) ?>
    <? else: ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/scm/' . $scm->id)) ?>
    <? endif; ?>
    </footer>
</form>
