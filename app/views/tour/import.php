<? use Studip\Button, Studip\LinkButton; ?>
<form class="default" enctype="multipart/form-data" action="<?= $controller->link_for('tour/import') ?>" method="post">
    <?= CSRFProtection::tokenTag(); ?>

    <fieldset>
        <legend><?= _('Importieren') ?></legend>

    <? if (!$tourdata) :  ?>
        <label>
            <span class="required"><?= _('Datei:') ?></span>
            <input type="file" size="60" name="tour_file"
                   required aria-required="true"
                   accept="application/json"
                   placeholder="<?= _('Bitte wählen Sie eine Quelldatei mit der Tour aus') ?>">
        </label>
    <? else : ?>
        <label>
            <span class="required"><?= _('Datei:') ?></span>
        </label>
        <div>
            <table class="default">
                <tr>
                    <td><?= _('Stud.IP Version') ?></td>
                    <td><?= htmlReady($metadata['version']) ?></td>
                </tr>
                <tr>
                    <td><?= _('Institution')?></td>
                    <td><?= htmlReady($metadata['source'] . ' (' . $metadata['url'] . ')')?></td>
                </tr>
                <tr>
                    <td><?= _('Sprache')?></td>
                    <td><?= htmlReady($tourdata['language']) ?></td>
                </tr>
                <tr>
                    <td><?= _('Startseite')?></td>
                    <td><?= htmlReady($tourdata['steps'][0]['route']) ?></td>
                </tr>
            </table>
        </div>
    <? endif ?>
    </fieldset>

    <footer data-dialog-button>
    <? if (!$tourdata) :  ?>
        <?= Studip\Button::create(_('Importieren'), 'import_file', ['data-dialog' => 'size=auto'])?>
    <? endif ?>
        <?= Studip\LinkButton::createCancel(_('Schließen'), $controller->url_for('tour/admin_overview')) ?>
    </footer>
</form>
