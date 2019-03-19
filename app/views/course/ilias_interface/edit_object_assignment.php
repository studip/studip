<form class="default" action="<?= $controller->url_for('course/ilias_interface/edit_object_assignment/'.$ilias_index) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table>
	<input type="hidden" name="ilias_module_id" value="<?=htmlReady($module_id)?>">
        <tr>
            <td>
                <input type="radio" id="ilias_add_mode_reference" name="ilias_add_mode" value="reference" checked>
            </td>
            <td>
                <label for="ilias_add_mode_reference">
                <?=_('Fügt einen Link zu diesem Objekt im ILIAS-Kurs ein. Änderungen am ursprünglichen Lernobjekt wirken sich auf alle verlinkten Vorkommen des Objekts aus.')?>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <input type="radio" id="ilias_add_mode_copy" name="ilias_add_mode" value="copy">
            </td>
            <td>
                <label for="ilias_add_mode_copy">
                <?=_('Erstellt eine neue Instanz des Objekts. Das kopierte Objekt ist zunächst offline, Sie müssen das Objekt daher in ILIAS erst auf "online" stellen, damit es für Teilnehmende der Veranstaltung sichtbar wird. Diese Option ist z.B. für Tests geeignet, weil sich eine Test-Instanz nicht mehr ändern lässt, sobald Lernende daran teilgenommen haben.')?>
                </label>
            </td>
        </tr>
    </table>
    <footer data-dialog-button>
        <?= Studip\Button::create(_('Bestätigen'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Schließen'), 'cancel', $dialog ? ['data-dialog' => 'close'] : []) ?>
    </footer>
</form>