<form name="approve"
      action="<?= $controller->url_for('studiengaenge/studiengangteile/approve/' . $stgteil_id . '/' . $version_id) ?>"
      method="post" style="margin-left: auto; margin-right: auto;">
    <? echo $this->render_partial('shared/studiengang/_stgteilversion', ['version' => $version]); ?>
    <? echo $this->render_partial('shared/version/_versionmodule', ['version' => $version]); ?>
    <div style="text-align: center;" data-dialog-button>
        <?= Studip\Button::createAccept(_('Genehmigen'), 'approval') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), ['data-dialog' => 'close']) ?>
    </div>
</form>