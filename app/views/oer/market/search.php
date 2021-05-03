<? if ($materialien) : ?>
    <ul class="oer_material_overview mainlist">
        <?= $this->render_partial("oer/market/_materials.php", compact("material", "plugin")) ?>
    </ul>
<? else : ?>
    <?= MessageBox::info(_('Keine Materialien gefunden')) ?>
<? endif ?>

<?
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _('Eigenes Lernmaterial hochladen'),
        $controller->url_for("oer/mymaterial/edit"),
        Icon::create("add", Icon::ROLE_CLICKABLE),
        ['data-dialog' => "1"]
    );
    Sidebar::Get()->addWidget($actions);
}
