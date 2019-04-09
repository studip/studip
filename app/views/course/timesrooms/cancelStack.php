<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/' . $cycle_id, $linkAttributes) ?>"
      class="default" data-dialog="size=big">
    <input type="hidden" name="method" value="preparecancel">
    <?= CSRFProtection::tokenTag()?>
    <?= $this->render_partial('course/timesrooms/_cancel_form.php') ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Übernehmen'), 'cancel') ?>
    <? if (Request::int('fromDialog')): ?>
        <?= Studip\LinkButton::create(_('Zurück zur Übersicht'),
              $controller->url_for('course/timesrooms/index'),
              ['data-dialog' => 'size=big']) ?>
    <? endif; ?>
    </footer>
</form>
