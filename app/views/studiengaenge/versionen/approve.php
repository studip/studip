<form name="approve" action="<?= $controller->url_for('/approve/' . $version_id) ?>" method="post"
      style="margin-left: auto; margin-right: auto;">
    <? echo $this->render_partial('shared/studiengang/_stgteilversion', ['version' => $version]); ?>
    <? echo $this->render_partial('shared/version/_versionmodule', ['version' => $version]); ?>
    <div style="text-align: center;" data-dialog-button>
        <?= CSRFProtection::tokenTag(); ?>
        <?= Studip\ Button::createAccept(_('Genehmigen'), 'approval', []) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>
</form>