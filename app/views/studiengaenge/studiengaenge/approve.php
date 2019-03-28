<form data-dialog="reload-on-close" name="approve" action="<?= $controller->url_for('/approve', $studiengang_id) ?>"
      method="post" style="margin-left: auto; margin-right: auto;">
    <?= $this->render_partial('shared/studiengang/_studiengang', ['studiengang' => $studiengang, 'institut' => $institut]); ?>
    <? if (count($studiengang->stgteil_assignments)) : ?>
        <h2><?= _('Studiengangteile') ?></h2>
        <? if (count($studiengang->stgteil_bezeichnungen)) : ?>
            <? foreach ($studiengang->stgteil_bezeichnungen as $stgteilbez) : ?>
                <h2><?= $stgteilbez->name; ?></h2>
                <div style="margin-left: 3em;">
                    <? foreach (StudiengangStgteil::findByStudiengangStgteilBez($studiengang->getId(), $stgteilbez->getId()) as $stgstgteil) : ?>
                        <? $stgteilbez_ids = $stgstgteil->getId(); ?>
                        <? $stgteil = StudiengangTeil::find($stgteilbez_ids[1]); ?>
                        <? if ($stgteil) : ?>
                            <?= $this->render_partial('shared/studiengang/_studiengangteil', ['stgteil' => $stgteil]); ?>
                            <? foreach (StgteilVersion::findByStgteil($stgteil->getId()) as $version) : ?>
                                <? if ($version->stat == 'genehmigt') : ?>
                                    <?= $this->render_partial('shared/studiengang/_stgteilversion', ['version' => $version]) ?>
                                <? endif; ?>
                            <? endforeach; ?>
                        <? endif; ?>
                    <? endforeach; ?>
                </div>
            <? endforeach; ?>
        <? else : ?>
            <? foreach ($studiengang->studiengangteile as $stgteil) : ?>
                <?= $this->render_partial('shared/studiengang/_studiengangteil', ['stgteil' => StudiengangTeil::find($stgteil->stgteil_id)]); ?>
            <? endforeach; ?>
        <? endif; ?>
    <? endif; ?>
    <?= CSRFProtection::tokenTag(); ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Genehmigen'), 'approval', []) ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), ['data-dialog' => 'close']) ?>
    </footer>
</form>