<form class="default" action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
<fieldset>
    <legend><?= _('Anlegen der Veranstaltung') ?></legend>

<? if ($dialog) : ?>
    <?= MessageBox::info(
        _('Sie haben alle benötigten Daten angegeben und können nun die Veranstaltung anlegen.')
    ) ?>
<? else : ?>
    <?= MessageBox::info(
        _('Sie haben alle benötigten Daten angegeben und können nun die Veranstaltung anlegen.')
        . ' ' .
        _('Der nächste Schritt führt Sie  gleich in den Verwaltungsbereich '
        . 'der neu angelegten Veranstaltung, wo Sie weitere Daten hinzufügen können.')
    ) ?>
<? endif ?>

<? if ($source_course) : ?>
    <section>
        <label>
            <input type="checkbox" checked name="copy_basic_data" value="1">
            <?= sprintf(
                _('Alle Grunddaten der Ursprungsveranstaltung (%s) kopieren'),
                sprintf(
                    '<a data-dialog href="%s">%s</a>',
                    URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $source_course->id]),
                    htmlReady($source_course->getFullname())
                )
            ) ?>
        </label>
    </section>
<? endif ?>

    <section>
        <input type="hidden" name="step" value="<?= $stepnumber ?>">
    <? if ($dialog) : ?>
        <input type="hidden" name="dialog" value="1">
    <? endif ?>
    </section>
</fieldset>

    <footer data-dialog-button>
    <? if ($_SESSION['coursewizard'][$this->temp_id]['batchcreate']) : ?>
        <? foreach ($_SESSION['coursewizard'][$this->temp_id]['batchcreate'] as $key => $value) : ?>
            <input type="hidden" name="batchcreate[<?= $key ?>]" value="<?= $value ?>">
        <? endforeach ?>
    <? endif ?>
        <?= Studip\Button::create(_('Zurück'), 'back',
            $dialog ? ['data-dialog' => 'size=50%'] : []) ?>
        <?= Studip\Button::createAccept(_('Veranstaltung anlegen'), 'create') ?>
    </footer>
</form>
