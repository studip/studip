<form class="default" action="<?= $controller->link_for('contents/courseware/create_project') ?>"
      method="post" enctype="multipart/form-data">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <label>
            <span>
                <?= _('Titel des Lernmaterials') ?>
            </span>
            <input required type="text" name="title">
        </label>

        <label>
            <span>
                <?= _('Zusammenfassung') ?>
            </span>
            <textarea name="description"></textarea>
        </label>

        <label class="col-3">
            <?= _('Art des Lernmaterials') ?>
            <select name="project_type">
                <option value="content">
                    <?= _('Inhalt') ?>
                </option>
                <option value="template">
                    <?= _('Vorlage') ?>
                </option>
                <option value="oer">
                    <?= _('OER-Material') ?>
                </option>
                <option value="portfolio">
                    <?= _('ePortfolio') ?>
                </option>
                <option value="draft">
                    <?= _('Entwurf') ?>
                </option>
                <option value="other">
                    <?= _('Sonstiges') ?>
                </option>
            </select>
        </label>

        <label class="col-3">
            <?= _('Lizenztyp') ?>
            <select name="license_type">
                <option value="Open Educational Resources">
                    <?= _('Open Educational Resources') ?>
                </option>
                <option value="Creative Commons">
                    <?= _('Creative Commons') ?>
                </option>
                <option value="Sonstiges">
                    <?= _('Sonstiges') ?>
                </option>
            </select>
        </label>

        <label class="col-3">
            <span>
                <?= _('Geschätzter zeitlicher Aufwand') ?>
            </span>
            <input type="text" name="required_time">
        </label>

        <label class="col-3">
            <span>
                <?= _('Farbe') ?>
            </span>
            <select name="color">
                <option value="royal-purple">
                    <?= _('Königliches Purpur') ?>
                </option>
                <option value="iguana-green">
                    <?= _('Leguangrün') ?>
                </option>
                <option value="charcoal">
                    <?= _('Holzkohle') ?>
                </option>
                <option value="queen-blue">
                    <?= _('Königin blau') ?>
                </option>
                <option value="verdigris">
                    <?= _('Helles Seegrün') ?>
                </option>
                <option value="mulberry">
                    <?= _('Maulbeere') ?>
                </option>
                <option value="pumpkin">
                    <?= _('Kürbis') ?>
                </option>
                <option value="apple-green">
                    <?= _('Apfelgrün') ?>
                </option>
                <option value="studip-blue">
                    <?= _('Blau') ?>
                </option>
                <option value="studip-gray">
                    <?= _('Grau') ?>
                </option>
                <option value="studip-green">
                    <?= _('Grün') ?>
                </option>
            </select>
        </label>

        <label class="file-upload">
            <?= _('Vorschaubild hochladen') ?>
            <input id="previewfile" name="previewfile" type="file" accept="image/*">
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen'), 'create_project', ['title' => _('Neues Lernmaterial erstellen')]) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('contents/courseware'), ['title' => _('Zurück zur Übersicht')]) ?>
    </footer>
</form>
