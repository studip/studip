<? if ($display): ?>
<p class="info">
<? if ($refered_from_seminar): ?>
    <?= sprintf(_('Hier können Sie die Daten der Veranstaltung <b>%s</b> direkt bearbeiten.'), htmlReady($name)) ?>
<? else: ?>
    <?= sprintf(_('Sie haben die Veranstaltung <b>%s</b> vorgewählt. Sie können nun direkt die einzelnen Bereiche dieser Veranstaltung bearbeiten, indem Sie die entsprechenden Menüpunkte wöhlen.'), htmlReady($name)) ?>
<? endif; ?>
</p>
<p class="info">
    <?= _('Wenn Sie eine andere Veranstaltung bearbeiten wollen, klicken Sie bitte auf <b>Veranstaltungen</b> um zum Auswahlmenü zurückzukehren.') ?>
</p>
<? endif; ?>
