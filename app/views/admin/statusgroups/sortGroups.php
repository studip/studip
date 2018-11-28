<noscript>
    <?= MessageBox::info(_('Leider ist es aus technischen Gründen nicht möglich ein vernünftiges Interface '
                         . 'ohne Javascript zu liefern. Nutzen sie bitte die Gruppierung unter den '
                         . 'Einstellungen der Gruppen oder aktivieren sie Javascript.')) ?>
</noscript>

<div class="ordering" title="<?= _('Gruppenreihenfolge ändern') ?>">
    <div class="nestable">
        <?= $this->render_partial('admin/statusgroups/_group-nestable.php', compact('groups')) ?>
    </div>
</div>

<form class="default" id="order_form" action="<?= $controller->url_for('admin/statusgroups/sortGroups') ?>" method="POST">
    <input type="hidden" name="ordering" id="ordering">
    
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'order') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/statusgroups/index')) ?>
    </footer>
</form>
